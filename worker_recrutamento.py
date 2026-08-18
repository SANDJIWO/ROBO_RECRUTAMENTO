import time
import logging
import sys
import os
import mysql.connector
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from urllib.parse import urlparse, parse_qs
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.edge.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.microsoft import EdgeChromiumDriverManager
from selenium.common.exceptions import WebDriverException

LOCK_FILE = "C:\\xampp\\htdocs\\robo_recrutamento\\robo.lock"

# --- CONFIGURAÇÃO DE LOG DUPLO (TERMINAL + ARQUIVO) ---
logger = logging.getLogger()
logger.setLevel(logging.INFO)
formatter = logging.Formatter('[%(asctime)s] %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

file_handler = logging.FileHandler('C:\\xampp\\htdocs\\robo_recrutamento\\robo_recrutamento.log', encoding='utf-8')
file_handler.setFormatter(formatter)
logger.addHandler(file_handler)

stream_handler = logging.StreamHandler()
stream_handler.setFormatter(formatter)
logger.addHandler(stream_handler)


def inicializar_driver():
    options = webdriver.EdgeOptions()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-extensions")
    options.add_argument("--remote-allow-origins=*")
    options.add_argument("--disable-software-rasterizer")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--ignore-certificate-errors")
    options.add_argument("--allow-insecure-localhost")
    options.add_argument("--disable-features=IsolateOrigins,site-per-process")
    
    service = Service(EdgeChromiumDriverManager().install())
    driver = webdriver.Edge(service=service, options=options)
    driver.set_page_load_timeout(60)
    return driver


def obter_email_recrutador_da_vaga(vaga_id):
    try:
        conn = mysql.connector.connect(
            host="localhost", user="root", password="", database="sistema_recrutamento_db"
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT email_recrutador FROM vagas WHERE id = %s", (vaga_id,))
        res = cursor.fetchone()
        cursor.close()
        conn.close()

        if res and res.get('email_recrutador'):
            return res['email_recrutador']
    except Exception as e:
        logging.error(f"[-] Erro ao buscar o e-mail do recrutador na BD: {str(e)}")
    
    return "recrutamento.empresa.exemplo@gmail.com"


def buscar_vagas_dinamicamente(perfil_id):
    vagas_lista = []
    try:
        conn = mysql.connector.connect(
            host="localhost", user="root", password="", database="sistema_recrutamento_db"
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id FROM vagas")
        resultados = cursor.fetchall()
        cursor.close()
        conn.close()

        for row in resultados:
            vaga_id = row['id']
            url = f"http://localhost/robo_recrutamento/ver_vaga.php?id={vaga_id}&perfil_id={perfil_id}"
            vagas_lista.append(url)
            
        logging.info(f"[+] Foram encontradas {len(vagas_lista)} vagas dinamicamente na base de dados para o perfil ID {perfil_id}.")
    except Exception as e:
        logging.error(f"[-] Erro ao buscar vagas da base de dados: {str(e)}")
    
    return vagas_lista


def enviar_email_candidatura(perfil, vaga_id, url_vaga, percentual_match, email_recrutador):
    smtp_server = "smtp.gmail.com"
    smtp_port = 587

    remetente_tecnico = os.getenv("ROBO_EMAIL", "jsandjiwo@gmail.com")
    password_tecnico = os.getenv("ROBO_PASSWORD", "omao iuum wfqd kwcd")
    
     
    destinatario = email_recrutador

    nome_candidato = perfil['dados_pessoais']['nome']
    email_candidato = perfil.get('email', 'N/A')
    hard_skills_str = ', '.join(perfil['competencias']['hard_skills'])

    assunto = f"[Robô Recrutamento] Nova Candidatura - Vaga ID {vaga_id} | Candidato: {nome_candidato} (Match: {percentual_match:.1f}%)"
    
    corpo_mensagem = f"""
Prezado Recrutador,

O agente autónomo mapeou e submeteu uma nova candidatura compatível com a sua vaga.

--- DADOS DO CANDIDATO ---
- Nome Completo: {nome_candidato}
- E-mail de Contato (Para Resposta): {email_candidato}
- Hard Skills: {hard_skills_str}

--- DETALHES DA OPORTUNIDADE ---
- Vaga ID: {vaga_id}
- URL da Vaga: {url_vaga}
- Nível de Aderência (Match): {percentual_match:.1f}%

Esta candidatura foi submetida automaticamente pelo Sistema de Recrutamento Autónomo em nome do candidato.
"""

    msg = MIMEMultipart()
    msg['From'] = remetente_tecnico
    msg['To'] = destinatario
    msg['Subject'] = assunto
    msg.attach(MIMEText(corpo_mensagem, 'plain', 'utf-8'))

    try:
        server = smtplib.SMTP(smtp_server, smtp_port)
        server.starttls()
        server.login(remetente_tecnico, password_tecnico)
        server.sendmail(remetente_tecnico, destinatario, msg.as_string())
        server.quit()
        logging.info(f"[+] E-mail de candidatura de '{nome_candidato}' ({email_candidato}) enviado com sucesso para a empresa: {destinatario}")
    except Exception as e:
        logging.error(f"[-] Erro ao enviar e-mail para a empresa: {str(e)}")


def obter_perfil_avancado(user_id):
    try:
        conn = mysql.connector.connect(
            host="localhost", user="root", password="", database="sistema_recrutamento_db"
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM perfil_candidato WHERE utilizador_id = %s", (user_id,))
        res = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if res:
            hard_skills_lista = [s.strip().upper() for s in res['hard_skills'].split(',')]
            return {
                "perfil_id": res['id'],
                "utilizador_id": res['utilizador_id'],
                "email": res.get('email', ''), 
                "dados_pessoais": {"nome": res['nome_completo']},
                "competencias": {"hard_skills": hard_skills_lista}
            }
    except Exception as e:
        logging.error(f"[-] Erro ao carregar perfil do banco de dados: {str(e)}")
    return None


def registrar_historico_no_banco(perfil_id, vaga_id, url_vaga, match_pct, status, erro_msg=None):
    try:
        conn = mysql.connector.connect(
            host="localhost", user="root", password="", database="sistema_recrutamento_db"
        )
        cursor = conn.cursor()
        sql = """INSERT INTO historico_candidaturas 
                 (vaga_id, url_vaga, percentual_match, status_submissao, detalhes_erro, perfil_id) 
                 VALUES (%s, %s, %s, %s, %s, %s)
                 ON DUPLICATE KEY UPDATE 
                 percentual_match = VALUES(percentual_match), 
                 detalhes_erro = VALUES(detalhes_erro),
                 executado_em = NOW(),
                 perfil_id = VALUES(perfil_id)"""
        cursor.execute(sql, (vaga_id, url_vaga, match_pct, status, erro_msg, perfil_id))
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        logging.error(f"[-] Erro ao salvar histórico: {str(e)}")


def analisar_e_submeter(driver, url_vaga, vaga_id, perfil_candidato):
    logging.info(f"[+] Acedendo ao ambiente de simulação local: {url_vaga}")
    perfil_id = perfil_candidato["perfil_id"]
    try:
        driver.get(url_vaga)
        wait = WebDriverWait(driver, 20)
        
        # Garante que o corpo da página carregou antes de procurar elementos
        wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        
        try:
            elementos_requisitos = wait.until(
                EC.presence_of_all_elements_located((By.XPATH, "//ul[@id='requisitos']/li"))
            )
        except Exception:
            logging.warning(f"[-] Aviso: Elemento com ID 'requisitos' não encontrado a tempo na vaga {vaga_id}.")
            elementos_requisitos = []
        
        requisitos_vaga = [req.text.strip().upper() for req in elementos_requisitos if req.text.strip()]
        logging.info(f"[*] Requisitos identificados na vaga: {requisitos_vaga}")
        
        skills_usuario = perfil_candidato["competencias"]["hard_skills"]
        correspondencias = [req for req in requisitos_vaga if req in skills_usuario]
        percentual_match = (len(correspondencias) / len(requisitos_vaga)) * 100 if requisitos_vaga else 0
        
        if percentual_match >= 50.0 or not requisitos_vaga:
            if not requisitos_vaga:
                percentual_match = 100.0  # Fallback caso a vaga não liste requisitos explicitamente
                
            logging.info(f"[+] Perfil adequado ({percentual_match:.1f}%). Submetendo e enviando ao recrutador...")
            
            registrar_historico_no_banco(perfil_id, vaga_id, url_vaga, percentual_match, 'Submetida')
            
            try:
                email_recrutador = obter_email_recrutador_da_vaga(vaga_id)
                enviar_email_candidatura(perfil_candidato, vaga_id, url_vaga, percentual_match, email_recrutador)
            except Exception as mail_err:
                logging.error(f"[-] Erro no envio de e-mail: {str(mail_err)}")
            
            try:
                botao = wait.until(EC.element_to_be_clickable((By.ID, "btnCandidatar")))
                driver.execute_script("arguments[0].scrollIntoView(true);", botao)
                time.sleep(1)
                botao.click()
                time.sleep(2)
                logging.info("[+] Sucesso: Candidatura submetida e e-mail enviado à empresa!")
            except Exception as btn_err:
                logging.warning(f"[-] Aviso: Botão 'btnCandidatar' não clicado ou ausente, mas match registado: {str(btn_err)}")
        else:
            logging.info(f"[-] Vaga ignorada ({percentual_match:.1f}% match): Requisitos insuficientes.")
            registrar_historico_no_banco(perfil_id, vaga_id, url_vaga, percentual_match, 'Ignorada')
            
    except WebDriverException as e:
        logging.error(f"[-] Exceção do WebDriver na vaga {vaga_id}: O navegador perdeu a conexão.")
        registrar_historico_no_banco(perfil_id, vaga_id, url_vaga, 0, 'Falha', "WebDriverException: Conexão perdida.")
    except Exception as e:
        logging.error(f"[-] Falha no processamento da vaga (ID {vaga_id}): {str(e)}")
        registrar_historico_no_banco(perfil_id, vaga_id, url_vaga, 0, 'Falha', str(e))


def main():
    if os.path.exists(LOCK_FILE):
        print("[-] O robô já se encontra em execução. Nova instância bloqueada.")
        return

    try:
        with open(LOCK_FILE, "w") as f:
            f.write(str(os.getpid()))

        logging.info("[*] Iniciando o Agente Autónomo...")
        
        user_id = int(sys.argv[1]) if len(sys.argv) > 1 else 4
        
        perfil = obter_perfil_avancado(user_id)
        if not perfil:
            logging.error(f"[-] Erro crítico: Impossível obter o perfil do candidato para o utilizador ID {user_id}.")
            return

        vagas_locais = buscar_vagas_dinamicamente(perfil['perfil_id'])
        if not vagas_locais:
            logging.warning("[-] Nenhuma vaga encontrada na base de dados para processar.")
            return
        
        driver = None
        try:
            driver = inicializar_driver()
            for url in vagas_locais:
                try:
                    parsed_url = urlparse(url)
                    captura_id = parse_qs(parsed_url.query).get('id')
                    
                    if captura_id:
                        vaga_id = int(captura_id[0])
                        analisar_e_submeter(driver, url, vaga_id, perfil)
                    else:
                        logging.error(f"[-] URL inválida para extração de ID: {url}")
                    
                    time.sleep(3)
                    
                except Exception as e:
                    logging.error(f"[-] Erro contornado na url {url}: {str(e)}")
                    continue
                    
        except Exception as e:
            logging.error(f"[-] Erro crítico na infraestrutura do robô: {str(e)}")
        finally:
            if driver:
                try:
                    driver.quit()
                except:
                    pass
            logging.info("[*] Ciclo do Agente Autónomo concluído com sucesso.")

    finally:
        if os.path.exists(LOCK_FILE):
            try:
                os.remove(LOCK_FILE)
            except:
                pass


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        logging.info("[*] Execução interrompida manualmente.")