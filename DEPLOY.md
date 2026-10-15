# Deploy — staging (push-to-deploy por SSH)

Staging: **https://vaga.admedia.pt** (Hostinger, atrás de Cloudflare).

**Fazer deploy = `git push origin staging`.** O GitHub Actions trata do resto:
build dos assets + dependências, envio por `rsync` sobre SSH, e `migrate` com o PHP 8.4
do servidor. Pipeline em `.github/workflows/deploy-staging.yml`.

```sh
git checkout staging
git merge main          # traz o que queres publicar
git push origin staging # ← dispara o deploy (ver em Actions)
```

## Estrutura no servidor
```
~/domains/vaga.admedia.pt/
├── public_html  ──➤  vaga/public        (symlink; é o docroot do Hostinger)
└── vaga/                                 (a app — estrutura normal do Laravel)
    ├── app/ config/ database/ vendor/ …
    ├── public/   (index.php, build/, storage→symlink)
    └── .env      (fica SÓ no servidor; nunca é enviado pelo deploy)
```
- PHP do host: web **8.4** (selecionado no hPanel); CLI 8.4 em `/opt/alt/php84/usr/bin/php`.
- Sem Redis/Meilisearch: cache/sessão em BD, fila `sync`, pesquisa com o motor `collection`.

## Segredos do GitHub (já configurados)
`SSH_HOST` `SSH_PORT` `SSH_USER` `SSH_APP_PATH` `SSH_KEY` — em *Settings → Secrets and variables → Actions*.
A chave privada do deploy está em `~/.ssh/vaga_deploy` (local); a pública está autorizada no servidor.

## Base de dados
Já importada (via dump). O `migrate` em cada deploy é aditivo (nunca apaga). Para repor os dados de
demonstração: `cd ~/domains/vaga.admedia.pt/vaga && /opt/alt/php84/usr/bin/php artisan migrate:fresh --seed --force`.

## Contas de teste (password: `password`)
`admin@vagacultural.pt` (admin) · `cultura@funchal.pt` (organização) · `teatro@funchal.pt` (promotor
de confiança) · `geral@raizes.pt` (promotor) · `user@vaga.pt` (user normal — criar com `migration-files/normal-user.sql`).

## Notas
- O domínio está atrás de **Cloudflare** → SSH é pelo **IP direto** do host, não pelo domínio.
- Um backup da estrutura FTP antiga ficou em `~/domains/vaga.admedia.pt/public_html_bak_*` — podes
  apagá-lo quando estiver tudo confirmado.
- Erro 500: `storage/logs/laravel.log`; ou `APP_DEBUG=true` no `.env` temporariamente.
