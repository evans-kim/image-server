# Swoole On-demand Image Proxy Server

## How to use
    https://[domain]/[image_path]@[options]
    
#### @option spec.

|param|Instruction|
|-----|-----------|
|w[int]|resize image based on width. ex) w150|
|h[int]|resize image based on height. ex) h150|

#### Examples

    https://image.candleworks.com/test-image.jpg@w400 -> resized
    https://image.candleworks.com/test-image.jpg -> original

## Install
Below instructions are executed on Ubuntu 18.04 
#### Swoole
    sudo apt-get install php php-dev php-xml
    sudo pecl install swoole
    
    php -i | grep php.ini                      # check the php.ini file location
    sudo echo "extension=swoole.so" >> /etc/php/7.2/cli/php.ini  # add the extension=swoole.so to the end of php.ini
    php -m | grep swoole   

#### Nginx
    sudo apt-get install nginx

###### Configure

    # nginx proxy server setting

    server {
        listen 80;
        root /home/ubuntu/default/current/public;
        server_name domain.com;
    
        location / {
            set $suffix ?$query_string;
    
            proxy_set_header Host $http_host;
            proxy_set_header Scheme $scheme;
            proxy_set_header SERVER_PORT $server_port;
            proxy_set_header REMOTE_ADDR $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            # if you need HTTPS
            # proxy_set_header HTTPS "on";
    
            proxy_pass http://127.0.0.1:9501$request_uri;
            proxy_cookie_path / /;
            expires 90d; # for browser cache
        }
    }

#### Deployment & Register service
    
    cd ~
    git clone https://github.com/evans-kim/image-server.git image-server
    cd image-server 
    chmod 755 service.sh
    vi service.ini 
    # update correct path WorkingDirectory=/home/ubuntu/image-server
    # update correct path ExecStart=/home/ubuntu/image-server/service.sh
    sudo cp ./service.ini /etc/systemd/system/image-proxy.service
    sudo service image-proxy start
    sudo service image-proxy status
    sudo service nginx reload
    sudo service nginx start
     

