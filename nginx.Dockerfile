FROM baserepo.pishtazteb.com/repo/devops/nginx:alpine


WORKDIR /var/www


COPY . /var/www
