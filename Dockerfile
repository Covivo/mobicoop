FROM ubuntu:18.04

ENV TZ=UTC
ENV PANTHER_NO_SANDBOX 1

ENV TERM xterm
ENV ZSH_THEME agnoster

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true

# Other
RUN mkdir ~/.ssh
RUN touch ~/.ssh_config
RUN mkdir /var/www
RUN mkdir /tools


RUN export LC_ALL=C.UTF-8
RUN DEBIAN_FRONTEND=noninteractive
RUN rm /bin/sh && ln -s /bin/bash /bin/sh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get -qq update && \
  echo "ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true" | debconf-set-selections && \
  apt-get -y -qq install software-properties-common &&\
  apt-add-repository "deb http://archive.canonical.com/ubuntu $(lsb_release -sc) partner" && \
  apt-add-repository ppa:malteworld/ppa && apt-get -qq update 

RUN apt-get install -y \
  sudo \
  zsh \
  autoconf \
  autogen \
  git \
  fonts-powerline \
  vim \ 
  nano \
  language-pack-en-base \
  wget \
  zip \
  unzip \
  curl \
  rsync \
  ssh \
  openssh-client \
  git \
  build-essential \
  apt-utils \
  software-properties-common \
  nasm \
  libjpeg-dev \
  libpng-dev \
  libpng16-16 \ 
  unzip \
  xvfb \
  libxss1 \
  libnss3 \
  libasound2 \
  libgtk-3-0 \
  libgtk2.0-0 \
  libgconf2-4 \
  ## chrome
  software-properties-common\
  adobe-flashplugin \
  msttcorefonts \
  fonts-noto-color-emoji \
  fonts-noto-cjk \
  fonts-liberation \
  fonts-thai-tlwg \
  fonts-indic \
  fontconfig \
  libappindicator3-1 \
  pdftk \
  locales \
  gconf-service \
  libasound2 \
  libatk1.0-0 \
  libc6 \
  libcairo2 \
  libcups2 \
  libdbus-1-3 \
  libexpat1 \
  libfontconfig1 \
  libgcc1 \
  libgconf-2-4 \
  libgdk-pixbuf2.0-0 \
  libglib2.0-0 \
  libgtk-3-0 \
  libnspr4 \
  libpango-1.0-0 \
  libpangocairo-1.0-0 \
  libstdc++6 \
  libx11-6 \
  libx11-xcb1 \
  libxcb1 \
  libxcomposite1 \
  libxcursor1 \
  libxdamage1 \
  libxext6 \
  libxfixes3 \
  libxi6 \
  libxrandr2 \
  libxrender1 \
  libxss1 \
  libxtst6 \
  ca-certificates \
  libappindicator1 \
  libnss3 \
  lsb-release \
  xdg-utils

RUN cd /tmp && wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb &&\
  dpkg -i google-chrome-stable_current_amd64.deb

RUN useradd -m docker && echo "docker:docker" | chpasswd && adduser docker sudo


# PHP
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php && apt-get update && apt-get install -y php7.2
RUN apt-get install -y \
  php7.2-curl \
  php7.2-gd \
  php7.2-dev \
  php7.2-bz2 \
  php7.2-cli \
  php7.2-xml \
  php7.2-bcmath \
  php7.2-mysql \
  php7.2-mbstring \
  php7.2-zip \
  php7.2-bz2 \
  php7.2-sqlite \
  php7.2-soap \
  php7.2-json \
  php7.2-intl \
  php7.2-imap \
  php7.2-imagick \
  php-memcached \
  php7.2-pgsql  \
  php7.2-readline \
  php7.2-soap \
  php7.2-bcmath \
  php7.2-xsl \
  php7.2-zip \
  php7.2-fpm 

RUN command -v php

RUN wget https://github.com/robbyrussell/oh-my-zsh/raw/master/tools/install.sh -O - | zsh || true

# Composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer && \
  chmod +x /usr/local/bin/composer && \
  composer self-update --preview
RUN command -v composer

RUN composer global require hirak/prestissimo

# Node.js
RUN curl -sL https://deb.nodesource.com/setup_11.x -o nodesource_setup.sh
RUN bash nodesource_setup.sh
RUN apt-get install nodejs -y
RUN npm install npm@6.9.0 -g
RUN command -v node
RUN command -v npm

RUN npm install -g wait-on 
RUN npm install -g yarn

# Request-dispatcher
RUN npm install -g request-dispatcher

# Cleanup
RUN apt-get -qq clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# Display versions installed
RUN php -v
RUN composer --version
RUN node -v
RUN npm -v
RUN yarn -v

CMD [ "zsh" ]
