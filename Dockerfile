FROM tiangolo/uwsgi-nginx:python3.5

COPY nginx.conf /etc/nginx/conf.d/
# COPY ssl-au1st3in.net.conf /etc/nginx/conf.d/
# COPY ssl-params.conf /etc/nginx/conf.d/
COPY ./app /app

RUN pip install --upgrade pip
RUN pip install --upgrade certifi
ADD . /app
WORKDIR /app
RUN python install_certifi.py
RUN pip install -r requirements.txt
