#!/bin/bash
#
# CHM Sistema - Configurações de Deploy
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#
# ATENÇÃO: Não commitar este arquivo com senhas reais
#

# FTP Napoleão
FTP_HOST="186.209.113.108"
FTP_PORT="21"
FTP_USER="chm-sistema@chm-sistema.com.br"
FTP_PASS="Ca258790%Ca258790%"
FTP_ROOT="/"

# URLs
APP_URL="https://chm-sistema.com.br"
DEPLOY_SECRET="chm_deploy_$(echo -n 'CHM-SISTEMA-2025' | md5sum | cut -d' ' -f1)"

# Banco de dados (para backup remoto)
DB_HOST="186.209.113.108"
DB_PORT="3306"
DB_NAME="chmtrans_chm-sistema"
DB_USER="chmtrans_chm-sistema"
# DB_PASS não logado por segurança
