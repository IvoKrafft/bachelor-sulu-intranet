FROM php:8.2.15-fpm

# Update system
RUN apt update
RUN apt upgrade -y

# Install programs and libaries
RUN apt install -y git
RUN apt install -y vim
RUN apt install -y libssl-dev
RUN apt install -y openssl

# Install composer
RUN apt install -y wget
RUN wget https://getcomposer.org/composer.phar
RUN mv composer.phar composer
RUN chmod +x composer
RUN mv composer /usr/local/bin

# Install MySQL client
RUN apt install -y mariadb-client
RUN docker-php-ext-install pdo_mysql

# Install imagick and image handling
RUN apt install -y libfreetype6-dev
RUN apt install -y libjpeg-dev
#RUN apt install -y libjpeg62-turbo-dev
#RUN apt install -y libmagickwand-dev
RUN apt install -y libpng-dev
#RUN pecl install imagick
#RUN docker-php-ext-enable imagick
RUN apt install -y zlib1g-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd


# Install curl
RUN apt install -y libcurl4-openssl-dev
RUN docker-php-ext-install curl


# Install NPM

# Install mbstring
RUN apt install -y libonig-dev
RUN docker-php-ext-install mbstring

# Install xml
RUN apt install -y libxml2-dev
RUN docker-php-ext-install xml

# Install zip
RUN apt install -y zlib1g-dev
RUN apt install -y libzip-dev
RUN docker-php-ext-install zip

# Other php extentions
RUN docker-php-ext-install bcmath
#RUN docker-php-ext-install exif
RUN docker-php-ext-install fileinfo
RUN docker-php-ext-install intl
#RUN docker-php-ext-install hash
#RUN docker-php-ext-install iconv
#RUN docker-php-ext-install json
#RUN docker-php-ext-install posix
#RUN docker-php-ext-install session
#RUN docker-php-ext-install simplexml
#RUN docker-php-ext-install soap
#RUN docker-php-ext-install sockets
#RUN docker-php-ext-install tokenizer

# Install NPM
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash
RUN apt install -y nodejs
RUN node -v
RUN npm -v

# Add ll alias
RUN echo "alias ll='ls $LS_OPTIONS -la'" >> /root/.bashrc

# Clean
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*
