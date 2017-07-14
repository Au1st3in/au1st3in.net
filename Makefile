REPO = au1st3in.net

.PHONY: all build run

all: build

build:
	docker rmi --force $(docker images -a -q)
	sudo docker build -t $(REPO) .
	docker login
	docker tag $(REPO) au1st3in/$(REPO)
	docker push au1st3in/$(REPO)

run:
	docker run -d --name flask -p 80:80 $(REPO)

syno:
	sed -i -e 's/80/81/' -e 's/443/444/' /usr/syno/share/nginx/server.mustache /usr/syno/share/nginx/DSM.mustache /usr/syno/share/nginx/WWWService.mustache
	synoservicecfg --restart nginx

	cd /usr/syno/etc/certificate/_archive/
	ln -s /usr/syno/etc/certificate/_archive/DznT3d /volume1/docker/cert

	nano /usr/syno/etc/packages/Docker/au1st3in.net.config