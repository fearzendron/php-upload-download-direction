<?php
    
    /*
     * Chave secreta deve ser usada em conjunto com o client que ira decriptografar
     */
    define('SALT', 'a melhor chave do universo');
    
    /*
     * Path de download do server do cliente
     */
    define('URL_DOWNLOAD_BOX_CLIENTE', 'http://172.17.4.128/Receptor/?hash=');
    define('URL_DOWNLOAD_BOX_CLIENTE_INTERNO', 'http://172.17.4.128/Receptor/?hash=');
    $path_download_padrao = URL_DOWNLOAD_BOX_CLIENTE;
    
    /*
     * Path de upload do server do cliente
     */
    define('URL_UPLOAD_BOX_CLIENTE', 'http://172.17.4.128/Receptor/upload_file.php');
    define('URL_UPLOAD_BOX_CLIENTE_INTERNO', 'http://172.17.4.128/Receptor/upload_file.php');
    $path_upload_padrao = URL_UPLOAD_BOX_CLIENTE;

    
    //Verifica se o acesso é do mesmo local do server
    verificaSeClienteInterno($path_download_padrao, $path_upload_padrao);
    
    
    function verificaSeClienteInterno(&$_path_download, &$_path_upload) {
        // PROCEDIMENTO PARA IDENTIFICAÇÃO SE O CLIENTE EXISTIR INTERNAMENTE OU EXTERNAMENTE DO CLIENTE
        // 1 - O software deve conhecer os ips dos clientes que são internos
        $ips_conhecidos = array("172.17.4.112"); //Exemplos

        // 2 - Realiza a verificação do usuario para ver se o cliente vem de um dos ips cohecidos
        if (in_array(get_ip_address(), $ips_conhecidos)) {
            $_path_download = URL_DOWNLOAD_BOX_CLIENTE_INTERNO;
            $_path_upload = URL_UPLOAD_BOX_CLIENTE_INTERNO;
        }
        
        echo "IP DO USUARIO: ".get_ip_address()."<br /><br />";
    }

    
    /*  Metodo de criptografia usando o mcrypt.so
     *  http://us.php.net/manual/en/mcrypt.constants.php
     *  http://php.net/manual/en/mcrypt.ciphers.phpreta
     */
    function encrypt($text) 
    { 
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, $iv); 
        return $crypttext; 
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
    
    
    /*
     * Recebe o post na mesma pagina e redireciona par ao box de acordo com a constante URL_BASE_BOX_CLIENTEFICTICIO
     */
    if ($_POST) {
        if (isset($_POST['ACAO']) && $_POST['ACAO'] == "download") {
            
            $nome_imagem = $_POST['imagem'];
            $time_now = microtime(true);
            
            $string_hash = $nome_imagem.":".$time_now;
            $hash =  encrypt($string_hash);
            $hash = urlencode($hash);//Realiza um encode para passar hash por parámetro
            
            //Testa se o serviço esta disponivel
            $retorno = @fsockopen("www.google.com.br", 80);
            if ($retorno < 1) { //
                echo "O servidor não esta respondendo!";
                exit;
            } else {
               
                header("Location: ".$path_download_padrao.$hash);
                exit;
            }
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>

        <form action="<?php echo $path_upload_padrao ?>" method="POST" enctype="multipart/form-data">
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="submit" value="Upload" />
        </form>
        
        <br /><br />
        
        <form method="POST" action="index.php">
            <input type="hidden" name="ACAO" value="download" />
            <input type="hidden" name="imagem" value="skyrim.jpg" />
            <input type="submit" value="Download" />
        </form>
        
    </body>
</html>
