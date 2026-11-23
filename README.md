# Luz Uberlândia — deploy WordPress na Hostinger

Este repositório contém o WordPress completo na raiz, incluindo o tema, o plugin
de privacidade e as regras das subpáginas. Não exige Node.js nem etapa de build.

## Configuração do deploy

- Repositório: `thiagonavarro/SiteLuzUberlandiaORG`
- Branch: `main`
- Diretório de origem: raiz (vazio ou `.`; não usar `luz-uberlandia`).
- Destino: `public_html` de `logical-white-wolf-19.hostingersite.com`.
- Hospedagem: PHP com MySQL, preferencialmente PHP 8.3 ou superior.

A integração Git da URL provisória já foi identificada com `main` e origem na raiz.
O WordPress 7.1.5 foi obtido da distribuição oficial e conferido com os checksums
da API wordpress.org. Os arquivos principais estão em `index.php`, `wp-admin`,
`wp-includes` e `wp-content`.

## Primeira instalação — uma vez por ambiente

1. Faça o deploy do conteúdo da raiz em `public_html`. Se houver um `index.html`
   antigo de outro site, preserve uma cópia e remova-o do destino; este projeto
   usa `index.php` como entrada.
2. Crie um banco MySQL e usuário no hPanel, caso ainda não existam.
3. Abra `https://logical-white-wolf-19.hostingersite.com/` e preencha a instalação
   oficial do WordPress com os dados do banco. Na Hostinger, o host interno do
   banco PHP costuma ser `localhost`; confirme o dado exibido no hPanel.
4. Defina o administrador e uma senha forte diretamente nessa tela. Nunca
   coloque credenciais no GitHub. `wp-config.php` é gerado no servidor e ignorado
   pelo Git; ele deve ser preservado nos próximos deploys.
5. Ao concluir uma instalação nova, o componente `luz-initial-setup.php` ativa
   o tema Luz Uberlândia e o plugin, cria `/contato/` e as duas políticas e
   configura os links permanentes. Ele não executa essa configuração em
   instalações WordPress que já existiam antes deste deploy.
6. Em instalação existente, ative o tema em Aparência e o plugin em Plugins;
   crie uma página Contato com `[luz_contato]` e salve os links permanentes.

O deploy prepara os arquivos; o banco e a conta administrativa continuam sendo
configurados na instalação. As páginas editáveis são armazenadas no banco,
não em pastas físicas com o nome de cada subpágina.

## Endereços após a instalação

- `/` — página inicial.
- `/contato/` — formulário.
- `/politica-de-privacidade/` — política editável em Páginas.
- `/politica-de-cookies/` — política editável em Páginas.
- `/wp-admin/` — administração.

O `.htaccess` encaminha as subpáginas ao WordPress. Caso uma subpágina retorne
404, salve **Configurações → Links permanentes** e confirme que o `.htaccess`
foi enviado ao servidor e que o suporte a regras de reescrita está ativo.

## Código do projeto

- [Tema](wp-content/themes/luz-uberlandia/)
- [Plugin de privacidade e contato](wp-content/plugins/luz-privacidade/)
- [Configuração de uma instalação nova](wp-content/mu-plugins/luz-initial-setup.php)
- [ZIP do tema](packages/luz-uberlandia.zip)
- [ZIP do plugin](packages/luz-privacidade.zip)
- [Verificações pendentes e operação](LEIA-ME.txt)

A URL provisória começa com indexação desativada. Na publicação definitiva,
revise Endereço do WordPress, Endereço do site e a opção de visibilidade.
Não foi alterado o site Horizons em `luzuberlandia.org.br`.

## Contato, privacidade e manutenção

O formulário encaminha nome, WhatsApp, e-mail e registro de autorização para
`contato@luzuberlandia.org.br`. Configure envio autenticado e confirme a entrega
antes de disponibilizá-lo. O WordPress aceitar o envio não garante entrega.
Não aplique cache de página prolongado à página do formulário.

Confirme o inventário real de cookies após instalar os componentes da hospedagem
e atualize a política conforme a configuração efetiva. Teste home, subpáginas,
banner e contato no servidor. A validação local não substitui teste com MySQL,
PHP e e-mail na hospedagem.

Mantenha o WordPress e os plugins atualizados, faça backup de banco e uploads
e reflita atualizações do núcleo neste repositório antes de novos deploys.
Dados do banco, uploads, configurações e segredos não pertencem ao Git.
