FROM misterio92/ci-php-node

RUN rm -rf /var/lib/apt/lists/*

RUN apt-get update && \
apt-get install -y --no-install-recommends \
	unzip \
	chromium-browser
ENV PANTHER_NO_SANDBOX 1