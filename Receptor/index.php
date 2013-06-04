<?php

    //A mesma chave que veio da origem
    define('SALT', 'a melhor chave do universo');
    
    //Garante que a requisição venha da Cloud
    $referer_permitido = "http://172.17.4.128/Teste/";
    
    //Itera nas posições do hash no 'explode'
    define('HASH_NOMEIMAGEM', 0);
    define('HASH_HORAENVIADA', 1);
    
    //Define a validade do hash
    define('TEMPO_HASH_EXPIRACAO', 20);

    /* 
     * Metodo de decriptografia usando o mcrypt.so
     * http://us.php.net/manual/en/mcrypt.constants.php
     * http://php.net/manual/en/mcrypt.ciphers.php
     */
    function decrypt($text) 
    {         
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, $iv); 
        return trim($decrypttext); 
    } 
    
    function get_ip_address() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

    
    if ($_GET && !empty($_SERVER['QUERY_STRING'])) {
        $v = $_GET['hash'];
        if (!empty($v)) {
            
           //Verificar Referer
            if ($_SERVER["HTTP_REFERER"] != $referer_permitido) {
                header("Location: 550.php");
                exit;  
            }

            // 2 - Decripta o hash e pega os dados e joga nas devidas variáveis.
            $hash_decript = decrypt(urldecode($v)); //Lembra de decodar a url para pegar o parâmetro
            $strings = explode(":", $hash_decript);
            $nome_imagem = trim($strings[HASH_NOMEIMAGEM]); //Nome da imagem no hash
            $time_de_envio = trim($strings[HASH_HORAENVIADA]); //Time de envio do hash
            
            $time_atual = microtime(true); //Obtem o time atual do servidor
            
            // 3 - Realiza a verificação do tempo de expiração da url de download
            if (($time_atual - intval($time_de_envio)) > TEMPO_HASH_EXPIRACAO) {
                //Enviar esta mensagem ao 550.php
                echo "<font color='#F00'>Url EXPIRADA!</font>";
            }
            
            // 4 - Encontra o arquivo e realiza o download para o usuário
            // Tamanho do arquivo
            $tamanho = filesize("$nome_imagem");

            // Obte a extensão
            $ext = explode (".",$nome_imagem);

            // Verificação de segurança para que não seja realizado o download de um arquivo php
            if ($ext[1]=="php") {
                echo "Arquivo não autorizado para download!";
            }

            // envia todos cabecalhos HTTP para o browser (tipo, tamanho, etc..)
            header("Content-Type: application/save"); 
            header("Content-Length: $tamanho");
            header("Content-Disposition: attachment; filename=$ext[0].$ext[1]"); 
            header("Content-Transfer-Encoding: binary");

            // nesse momento ele le o arquivo e envia
            // TODO: montar o path de download com $ip_de_download /////// <-=---------=-=-=-=-=
            $fp = fopen("$nome_imagem", "r"); 
            fpassthru($fp); 
            fclose($fp);
                
            echo $nome_imagem;
            exit;
            
        } else {
            header("Location: 550.php");
            exit;
        }
    } else {
        header("Location: 550.php");
        exit;
    }
    
    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        Receptor
    </body>
</html>
