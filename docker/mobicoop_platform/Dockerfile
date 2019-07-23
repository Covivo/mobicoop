FROM mobicoop/php-node-chromium:2.0.0
RUN apt update
RUN curl --location --output /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 && \
   chmod +x /usr/local/bin/mhsendmail
RUN apt-get -y install sendmail sendmail-cf m4
RUN hostname >> /etc/mail/relay-domains
RUN m4 /etc/mail/sendmail.mc > /etc/mail/sendmail.cf
RUN /etc/init.d/sendmail restart

