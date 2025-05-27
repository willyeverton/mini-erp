# Mini-ERP

Um sistema completo de gestão empresarial (ERP) com recursos integrados de e-commerce, desenvolvido em PHP com o framework CodeIgniter. Este sistema inclui módulos de produtos, estoque, pedidos, cupons, clientes e relatórios, fornecendo uma solução completa para pequenas e médias empresas.

![Mini-ERP Screenshot](path/to/screenshot.png)

## Índice
- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Módulos](#módulos)
- [API REST](#api-rest)
- [Customização](#customização)
- [Segurança](#segurança)
- [Solução de Problemas](#solução-de-problemas)
- [Licença](#licença)

## Visão Geral

Mini-ERP é uma solução completa para gerenciamento empresarial que combina funcionalidades essenciais de um ERP com capacidades de e-commerce. Desenvolvido com foco na usabilidade e desempenho, o sistema oferece uma interface amigável e responsiva para administração de todos os aspectos do negócio.

## Funcionalidades

### Gerais
- Dashboard interativo com visualização de dados e gráficos
- Autenticação e autorização com diferentes níveis de acesso
- Interface responsiva baseada no Bootstrap
- Exportação de relatórios em CSV e PDF

### Produtos
- Cadastro completo de produtos com imagens
- Suporte a variações de produtos (tamanho, cor, etc.)
- Gerenciamento avançado de estoque
- Produtos em destaque

### Pedidos
- Fluxo completo de pedidos (pendente, processando, enviado, entregue)
- Integração com consulta de CEP
- Cálculo automático de frete
- Geração de nota fiscal e confirmação por email

### Cupons
- Criação de cupons de desconto por porcentagem ou valor fixo
- Definição de valor mínimo de compra
- Limite de uso por cupom
- Período de validade configurável

### Clientes
- Cadastro de clientes com endereço e dados completos
- Histórico de compras
- Área do cliente para acompanhamento de pedidos
- Gerenciamento de carrinhos de compra

### Relatórios
- Análise de vendas por período
- Produtos mais vendidos
- Status de pedidos
- Aquisição de clientes
- Relatórios exportáveis em CSV e PDF

## Requisitos

- PHP 7.3 ou superior
- MySQL 5.7 ou superior
- Extensão PHP GD (para manipulação de imagens)
- Extensão PHP cURL (para integrações externas)
- Extensão PHP Intl (para formatação de moeda e datas)
- SMTP configurado para envio de emails
- Composer (opcional, para gerenciar dependências)

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/mini-erp.git
   cd mini-erp
   ```

2. Crie o banco de dados:
   ```sql
   CREATE DATABASE mini_erp CHARACTER SET utf8 COLLATE utf8_general_ci;
   ```

3. Importe o esquema do banco de dados:
   ```bash
   mysql -u seu_usuario -p mini_erp < database.sql
   ```

4. Configure as credenciais do banco de dados em `application/config/database.php`:
   ```php
   $db['default'] = array(
       'dsn'          => '',
       'hostname'     => 'localhost',
       'username'     => 'seu_usuario',
       'password'     => 'sua_senha',
       'database'     => 'mini_erp',
       'dbdriver'     => 'mysqli',
       // outras configurações...
   );
   ```

5. Configure a URL base em `application/config/config.php`:
   ```php
   $config['base_url'] = 'http://seu-dominio.com/';
   ```

6. Crie diretórios com permissões de escrita:
   ```bash
   mkdir -p assets/images/products
   chmod 777 assets/images/products
   mkdir -p application/logs
   chmod 777 application/logs
   mkdir -p application/cache
   chmod 777 application/cache
   mkdir -p application/cache/sessions
   chmod 777 application/cache/sessions
   ```

## Configuração

### Configuração de Email

Edite o arquivo `application/config/email.php`:

```php
$config = array(
    'protocol'      => 'smtp',
    'smtp_host'     => 'smtp.seu-provedor.com',
    'smtp_port'     => 587,
    'smtp_user'     => 'seu-email@seu-provedor.com',
    'smtp_pass'     => 'sua-senha',
    'smtp_crypto'   => 'tls',
    'mailtype'      => 'html',
    'charset'       => 'utf-8',
    'newline'       => "\r\n",
    'wordwrap'      => TRUE
);
```

Para ambiente de desenvolvimento, você pode usar o Mailtrap:

```php
$config = array(
    'protocol'      => 'smtp',
    'smtp_host'     => 'smtp.mailtrap.io',
    'smtp_port'     => 2525,
    'smtp_user'     => 'seu_usuario_mailtrap',
    'smtp_pass'     => 'sua_senha_mailtrap',
    'smtp_crypto'   => '',
    'mailtype'      => 'html',
    'charset'       => 'utf-8',
    'newline'       => "\r\n",
    'wordwrap'      => TRUE
);
```

### Chave de Criptografia

Defina uma chave de criptografia forte em `application/config/config.php`:

```php
$config['encryption_key'] = 'gere_uma_chave_forte_aqui';
```

## Estrutura do Projeto

```
mini-erp/
├── application/                # Pasta principal da aplicação CodeIgniter
│   ├── cache/                  # Cache de arquivos
│   ├── config/                 # Arquivos de configuração
│   ├── controllers/            # Controladores
│   ├── core/                   # Classes de núcleo estendidas
│   ├── helpers/                # Helpers personalizados
│   ├── libraries/              # Bibliotecas personalizadas
│   ├── models/                 # Modelos
│   ├── third_party/            # Bibliotecas de terceiros
│   └── views/                  # Views (templates)
│       ├── cart/               # Views do carrinho
│       ├── coupons/            # Views de cupons
│       ├── dashboard/          # Views do dashboard
│       ├── orders/             # Views de pedidos
│       ├── products/           # Views de produtos
│       ├── templates/          # Templates compartilhados
│       └── users/              # Views de usuários
├── assets/                     # Recursos estáticos
│   ├── css/                    # Arquivos CSS
│   ├── images/                 # Imagens
│   │   ├── products/           # Imagens de produtos
│   └── js/                     # Arquivos JavaScript
│       ├── components/         # Componentes JS reutilizáveis
│       ├── dashboard/          # Scripts do dashboard
│       ├── products/           # Scripts de produtos
│       ├── orders/             # Scripts de pedidos
│       └── utils/              # Utilitários JS
├── system/                     # Núcleo do CodeIgniter
├── .htaccess                   # Configurações do Apache
├── database.sql                # Esquema do banco de dados
├── index.php                   # Arquivo de entrada do sistema
└── README.md                   # Este arquivo
```

## Módulos

### Dashboard

O Dashboard apresenta uma visão geral do sistema com indicadores de desempenho e gráficos:

- Resumo de vendas no período
- Total de pedidos
- Receita total
- Total de produtos cadastrados
- Total de clientes
- Produtos mais vendidos
- Produtos com estoque baixo
- Gráficos de desempenho

Para acessar:
```
http://seu-dominio.com/dashboard
```

### Produtos

O módulo de produtos permite gerenciar todo o catálogo:

- CRUD completo de produtos
- Upload de imagens
- Gerenciamento de variações (tamanho, cor, etc.)
- Controle de estoque para produtos e variações
- Marcação de produtos em destaque

Operações principais:
```
http://seu-dominio.com/products                # Listar produtos
http://seu-dominio.com/products/create         # Criar produto
http://seu-dominio.com/products/edit/ID        # Editar produto
http://seu-dominio.com/products/view/ID        # Visualizar produto
http://seu-dominio.com/products/delete/ID      # Excluir produto
```

### Cupons

Gerenciamento completo de cupons de desconto:

- Criação de cupons por porcentagem ou valor fixo
- Definição de valor mínimo para aplicação
- Limite máximo de desconto
- Controle de validade (início/término)
- Limite de uso por cupom

Operações principais:
```
http://seu-dominio.com/coupons                 # Listar cupons
http://seu-dominio.com/coupons/create          # Criar cupom
http://seu-dominio.com/coupons/edit/ID         # Editar cupom
http://seu-dominio.com/coupons/view/ID         # Visualizar cupom
http://seu-dominio.com/coupons/delete/ID       # Excluir cupom
```

### Pedidos

Gestão completa do ciclo de pedidos:

- Fluxo de checkout
- Aplicação de cupons
- Cálculo de frete
- Consulta de endereço via CEP
- Rastreamento de status
- Visualização detalhada do pedido

Operações principais:
```
http://seu-dominio.com/orders                  # Listar pedidos
http://seu-dominio.com/orders/view/ID          # Visualizar pedido
http://seu-dominio.com/orders/checkout         # Finalizar compra
http://seu-dominio.com/orders/success/ID       # Confirmação de pedido
```

### Usuários

Gerenciamento de usuários do sistema:

- Criação e edição de usuários
- Definição de perfis (admin, customer)
- Alteração de senha
- Recuperação de senha

Operações principais:
```
http://seu-dominio.com/users                   # Listar usuários
http://seu-dominio.com/users/create            # Criar usuário
http://seu-dominio.com/users/edit/ID           # Editar usuário
http://seu-dominio.com/users/view/ID           # Visualizar usuário
```

### Carrinho

Gerenciamento do carrinho de compras:

- Adição de produtos
- Atualização de quantidades
- Remoção de itens
- Aplicação de cupons de desconto
- Cálculo de subtotal, frete e total

Operações principais:
```
http://seu-dominio.com/cart                    # Visualizar carrinho
http://seu-dominio.com/cart/add                # Adicionar ao carrinho
http://seu-dominio.com/cart/update             # Atualizar carrinho
http://seu-dominio.com/cart/remove/ID          # Remover item
http://seu-dominio.com/cart/clear              # Limpar carrinho
```

## Customização

### Temas

O sistema utiliza Bootstrap 4 como framework CSS. Para personalizar a aparência:

1. Modifique os arquivos CSS em `assets/css/`
2. Edite os templates em `application/views/templates/`

### Configurações Gerais

As principais configurações estão em:
- `application/config/config.php`: Configurações gerais
- `application/config/database.php`: Configuração do banco de dados
- `application/config/routes.php`: Rotas do sistema

### Adicionando Novos Módulos

Para adicionar novos módulos ao sistema:

1. Crie um controlador em `application/controllers/`
2. Crie modelos relacionados em `application/models/`
3. Crie views em `application/views/seu-modulo/`
4. Adicione as rotas em `application/config/routes.php`
5. Adicione links no menu lateral em `application/views/templates/sidebar.php`

## Segurança

### Proteção Contra CSRF

O sistema utiliza proteção contra CSRF (Cross-Site Request Forgery). Em todos os formulários, inclua:

```php
<?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
```

### Senhas

As senhas são armazenadas com hash usando bcrypt. O sistema inclui funcionalidades de:
- Recuperação de senha
- Expiração de token de reset
- Validação de força de senha

### Níveis de Acesso

O sistema possui dois níveis de acesso principais:
- `admin`: Acesso completo ao sistema
- `customer`: Acesso limitado à área do cliente

Os controles de acesso são implementados em `MY_Controller.php`.

## Solução de Problemas

### Emails não estão sendo enviados

1. Verifique as configurações em `application/config/email.php`
2. Para testes, configure o Mailtrap.io como descrito na seção de Configuração
3. Verifique se o servidor tem as permissões necessárias para enviar emails

### Erros ao excluir registros

O sistema implementa verificações de integridade referencial. Se um registro está sendo usado em outro lugar, não será possível excluí-lo. A mensagem de erro indicará qual relacionamento está impedindo a exclusão.

### Problemas com upload de imagens

1. Verifique se o diretório `assets/images/products/` tem permissões de escrita
2. Confirme que a extensão GD do PHP está habilitada
3. Verifique os limites de tamanho de upload no PHP (`php.ini`)

### Erro na validação do formulário de checkout

Se você encontrar problemas com a validação do formulário de checkout após o preenchimento automático pelo ViaCEP:

1. Verifique se o JavaScript está disparando eventos `change` nos campos após o preenchimento automático
2. Certifique-se de que os nomes dos campos no formulário correspondem exatamente aos esperados pelo controller

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

---

Desenvolvido com ❤️ usando CodeIgniter, jQuery e Bootstrap.
