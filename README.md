# CEP Clientes — Full Stack

Aplicação Full Stack para consulta de CEP, preenchimento automático de endereço e cadastro de clientes.

O projeto foi desenvolvido para um teste técnico Full Stack com foco em:

- backend em PHP/Laravel;
- frontend em React + TypeScript + Vite;
- consulta real de CEP;
- cadastro, listagem, edição e exclusão de clientes;
- persistência em banco de dados;
- ambiente local com Docker Compose;
- validações, tratamento de erros, cache e testes automatizados;
- estrutura simples, organizada e defensável em entrevista.

---

## Visão geral

O requisito original solicita integração com a **API Busca CEP dos Correios**. Durante a implementação, a API oficial dos Correios exigiu liberação contratual específica da **API 41 / Busca CEP**. Por isso, o backend foi estruturado com uma camada de providers de CEP:

- `viacep`: provider principal no ambiente local, com consulta real e sem necessidade de token;
- `correios`: provider preparado para a API oficial dos Correios, ativável por `.env` quando o contrato tiver a API liberada;
- `fake`: provider usado apenas em testes automatizados e desenvolvimento offline.

Essa decisão mantém a aplicação funcional, testável e extensível, sem acoplar o código a um único fornecedor externo.

---

## Status atual

Backend validado manualmente e por testes automatizados.

```txt
GET    /api/health              OK
GET    /api/ceps/81530000       OK com ViaCEP real
POST   /api/customers           OK
GET    /api/customers           OK
PUT    /api/customers/{id}      OK
PATCH  /api/customers/{id}      OK
DELETE /api/customers/{id}      OK
Cache de CEP                    OK
Testes automatizados            22 passed / 105 assertions
```

Frontend validado em build de produção.

```txt
React + TypeScript + Vite       OK
Tela única                      OK
Cadastro                        OK
Listagem                        OK
Edição em modal                 OK
Exclusão com confirmação         OK
Busca automática de CEP          OK
Design responsivo                OK
Build de produção                OK
```

---

## Stack

### Backend

- PHP 8.3
- Laravel 13
- MySQL 8.4
- Nginx
- Docker Compose
- PHPUnit
- ViaCEP como provider principal local
- Provider Correios preservado para uso futuro

### Frontend

- React
- TypeScript
- Vite
- CSS puro
- Fetch API nativo
- Design responsivo
- Tela única com modais para edição, confirmação e feedback

---

## Estrutura do projeto

```txt
cep-clientes/
├── backend/
│   ├── app/
│   │   ├── DTOs/
│   │   │   └── CepAddressData.php
│   │   ├── Exceptions/
│   │   │   ├── CepNotFoundException.php
│   │   │   └── CepProviderException.php
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   │   ├── CepController.php
│   │   │   │   └── CustomerController.php
│   │   │   └── Requests/
│   │   │       ├── StoreCustomerRequest.php
│   │   │       └── UpdateCustomerRequest.php
│   │   ├── Models/
│   │   │   ├── CepCache.php
│   │   │   └── Customer.php
│   │   ├── Providers/
│   │   │   └── AppServiceProvider.php
│   │   └── Services/Cep/
│   │       ├── CepProviderInterface.php
│   │       ├── CepService.php
│   │       ├── CorreiosCepProvider.php
│   │       ├── CorreiosTokenService.php
│   │       ├── FakeCepProvider.php
│   │       └── ViaCepProvider.php
│   ├── config/
│   │   ├── app.php
│   │   ├── cache.php
│   │   ├── cep.php
│   │   ├── cors.php
│   │   └── database.php
│   ├── database/migrations/
│   ├── routes/api.php
│   ├── tests/
│   │   ├── Feature/
│   │   │   ├── CepApiTest.php
│   │   │   ├── CepLookupTest.php
│   │   │   ├── CorreiosCepProviderTest.php
│   │   │   ├── CustomerApiTest.php
│   │   │   └── CustomerTest.php
│   │   └── TestCase.php
│   ├── composer.json
│   ├── phpunit.xml
│   ├── .env.example
│   └── .env
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       ├── Dockerfile
│       └── entrypoint.sh
├── frontend/
│   ├── index.html
│   ├── package.json
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── tsconfig.app.json
│   ├── tsconfig.node.json
│   ├── .env.example
│   └── src/
│       ├── App.tsx
│       ├── main.tsx
│       ├── styles.css
│       ├── config/
│       │   └── env.ts
│       ├── services/
│       │   └── api.ts
│       ├── types/
│       │   └── customer.ts
│       └── components/
│           ├── CustomerForm.tsx
│           ├── CustomerList.tsx
│           └── FeedbackModal.tsx
├── docker-compose.yml
└── README.md
```

---

## Requisitos para executar

Antes de começar, tenha instalado:

- Docker Desktop;
- Docker Compose;
- Node.js 20+ ou 22+;
- npm;
- Git;
- PowerShell, CMD, Git Bash ou terminal similar.

Portas usadas no ambiente local:

```txt
Backend/Nginx: http://localhost:8081
Frontend/Vite: http://localhost:5173
MySQL:         localhost:3307
```

Se alguma dessas portas estiver em uso, ajuste o `docker-compose.yml` ou a configuração do Vite.

---

## Instalação rápida

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd cep-clientes
```

### 2. Subir o backend com Docker

Na raiz do projeto:

```bash
docker compose up -d --build
```

Esse comando sobe:

- container da API PHP/Laravel;
- container Nginx;
- container MySQL.

O `entrypoint` do container da API executa automaticamente:

- `composer install`, se `vendor/` não existir;
- cópia de `.env.example` para `.env`, se `.env` não existir;
- geração de `APP_KEY`, se necessário;
- espera pelo MySQL;
- execução das migrations.

A API ficará disponível em:

```txt
http://localhost:8081
```

### 3. Verificar se a API subiu

```bash
curl http://localhost:8081/api/health
```

Resposta esperada:

```json
{
  "status": "ok",
  "app": "CEP Clientes API",
  "environment": "local"
}
```

No PowerShell:

```powershell
curl.exe -i http://localhost:8081/api/health
```

### 4. Configurar e rodar o frontend

Em outro terminal:

```bash
cd frontend
npm install
```

Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

No PowerShell, use:

```powershell
Copy-Item .env.example .env
```

O arquivo `frontend/.env` deve conter:

```env
VITE_API_BASE_URL=http://localhost:8081/api
```

Execute o frontend:

```bash
npm run dev
```

Acesse:

```txt
http://localhost:5173
```

---

## Configuração do backend

Arquivo principal:

```txt
backend/.env
```

Arquivo de exemplo versionado:

```txt
backend/.env.example
```

Configuração local recomendada:

```env
APP_NAME="CEP Clientes API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8081

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cep_clientes
DB_USERNAME=cep_user
DB_PASSWORD=cep_password

CACHE_STORE=database
QUEUE_CONNECTION=sync
SESSION_DRIVER=array

CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173

CEP_PROVIDER=viacep
CEP_FALLBACK_PROVIDER=correios

VIACEP_BASE_URL=https://viacep.com.br/ws
VIACEP_TIMEOUT=10
```

---

## Providers de CEP

O backend usa a interface:

```php
App\Services\Cep\CepProviderInterface
```

Providers disponíveis:

```txt
viacep
correios
fake
```

### Provider principal local: ViaCEP

No `.env`:

```env
CEP_PROVIDER=viacep
CEP_FALLBACK_PROVIDER=correios
```

Esse é o modo recomendado para rodar o projeto localmente porque:

- funciona sem credenciais;
- consulta CEP real;
- permite validar o fluxo completo;
- mantém o provider oficial dos Correios pronto para ativação futura.

### Provider oficial dos Correios

O provider dos Correios está implementado, mas para funcionar em produção é necessário que a conta/contrato tenha acesso à API Busca CEP dos Correios.

Configuração base:

```env
CEP_PROVIDER=correios

CORREIOS_BASE_URL=https://api.correios.com.br
CORREIOS_TOKEN_BASE_URL=https://api.correios.com.br
CORREIOS_CEP_ENDPOINT=/cep/v2/enderecos/{cep}

CORREIOS_AUTH_TYPE=contrato
CORREIOS_USERNAME=seu_usuario_meu_correios
CORREIOS_PASSWORD=seu_codigo_de_acesso_api
CORREIOS_CONTRACT_NUMBER=numero_do_contrato
CORREIOS_CONTRACT_DR=10

CORREIOS_BEARER_TOKEN=
```

Também é possível usar um Bearer Token já existente:

```env
CEP_PROVIDER=correios
CORREIOS_BEARER_TOKEN=seu_token_valido
```

Quando `CORREIOS_BEARER_TOKEN` está preenchido, o sistema não chama o endpoint de geração de token.

### Provider fake

Usado apenas para testes automatizados:

```env
CEP_PROVIDER=fake
```

Não use o provider fake como modo principal da aplicação.

---

## Funcionalidades do frontend

A interface foi mantida em uma única tela, com modais apenas quando necessário.

Funcionalidades:

- formulário de cadastro de cliente;
- busca automática do CEP ao digitar 8 dígitos;
- preenchimento automático de logradouro, bairro, cidade e UF;
- listagem dos clientes cadastrados;
- edição de cliente em modal;
- exclusão de cliente com modal de confirmação;
- mensagens de sucesso e erro em modal;
- layout responsivo para desktop, tablet e celular;
- CSS puro, sem biblioteca pesada de UI;
- consumo da API usando `fetch`.

Fluxo de uso:

1. O usuário informa nome, e-mail e CEP.
2. Ao completar o CEP, o frontend chama o backend.
3. O backend consulta cache/provider e retorna o endereço.
4. O frontend preenche logradouro, bairro, cidade e UF.
5. O usuário informa número e complemento, se houver.
6. O cadastro é salvo.
7. A lista é atualizada automaticamente.
8. O usuário pode editar ou excluir registros existentes.

---

## Endpoints da API

### Health check

```http
GET /api/health
```

Exemplo:

```bash
curl http://localhost:8081/api/health
```

---

### Buscar CEP

```http
GET /api/ceps/{cep}
```

Exemplo:

```bash
curl http://localhost:8081/api/ceps/81530000
```

Resposta real validada:

```json
{
  "cep": "81530-000",
  "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
  "bairro": "Jardim das Américas",
  "cidade": "Curitiba",
  "uf": "PR"
}
```

Possíveis erros:

```json
{
  "message": "CEP deve conter exatamente 8 dígitos."
}
```

```json
{
  "message": "CEP não encontrado no ViaCEP."
}
```

```json
{
  "message": "Não foi possível consultar o CEP no ViaCEP."
}
```

```json
{
  "message": "Token dos Correios recusado. Verifique credenciais, contrato e liberação da API Busca CEP."
}
```

---

### Listar cadastros

```http
GET /api/customers
```

Exemplo:

```bash
curl http://localhost:8081/api/customers
```

Resposta:

```json
{
  "data": [
    {
      "id": 1,
      "nome": "Maria Oliveira",
      "email": "maria.teste@example.com",
      "cep": "81530-000",
      "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
      "numero": "250",
      "complemento": null,
      "bairro": "Jardim das Américas",
      "cidade": "Curitiba",
      "uf": "PR",
      "created_at": "2026-05-21T00:33:49.000000Z",
      "updated_at": "2026-05-21T00:33:49.000000Z"
    }
  ]
}
```

---

### Criar cadastro

```http
POST /api/customers
```

Exemplo:

```bash
curl -X POST http://localhost:8081/api/customers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "Maria Oliveira",
    "email": "maria.teste@example.com",
    "cep": "81530000",
    "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
    "numero": "250",
    "complemento": "",
    "bairro": "Jardim das Américas",
    "cidade": "Curitiba",
    "uf": "PR"
  }'
```

Resposta esperada:

```json
{
  "message": "Cadastro criado com sucesso.",
  "data": {
    "id": 1,
    "nome": "Maria Oliveira",
    "email": "maria.teste@example.com",
    "cep": "81530-000",
    "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
    "numero": "250",
    "complemento": null,
    "bairro": "Jardim das Américas",
    "cidade": "Curitiba",
    "uf": "PR"
  }
}
```

---

### Atualizar cadastro

```http
PUT /api/customers/{id}
```

Exemplo:

```bash
curl -X PUT http://localhost:8081/api/customers/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "Maria Oliveira Atualizada",
    "email": "maria.atualizada@example.com",
    "cep": "81530000",
    "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
    "numero": "500",
    "complemento": "Apto 21",
    "bairro": "Jardim das Américas",
    "cidade": "Curitiba",
    "uf": "PR"
  }'
```

Resposta esperada:

```json
{
  "message": "Cadastro atualizado com sucesso.",
  "data": {
    "id": 1,
    "nome": "Maria Oliveira Atualizada",
    "email": "maria.atualizada@example.com",
    "cep": "81530-000",
    "logradouro": "Avenida Coronel Francisco Heráclito dos Santos",
    "numero": "500",
    "complemento": "Apto 21",
    "bairro": "Jardim das Américas",
    "cidade": "Curitiba",
    "uf": "PR"
  }
}
```

---

### Atualizar parcialmente

```http
PATCH /api/customers/{id}
```

Exemplo:

```bash
curl -X PATCH http://localhost:8081/api/customers/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "numero": "999",
    "complemento": "Bloco B"
  }'
```

---

### Excluir cadastro

```http
DELETE /api/customers/{id}
```

Exemplo:

```bash
curl -X DELETE http://localhost:8081/api/customers/1 \
  -H "Accept: application/json"
```

Resposta esperada:

```json
{
  "message": "Cadastro excluído com sucesso."
}
```

---

## Exemplo seguro para PowerShell com UTF-8 sem BOM

No PowerShell, para evitar problemas com acentos no JSON:

```powershell
$body = @{
  nome = "Maria Oliveira"
  email = "maria.teste@example.com"
  cep = "81530000"
  logradouro = "Avenida Coronel Francisco Heráclito dos Santos"
  numero = "250"
  complemento = ""
  bairro = "Jardim das Américas"
  cidade = "Curitiba"
  uf = "PR"
} | ConvertTo-Json -Depth 5

$jsonFile = Join-Path $env:TEMP "cep-clientes-customer.json"
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($jsonFile, $body, $utf8NoBom)

curl.exe -i -X POST "http://localhost:8081/api/customers" `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  --data-binary "@$jsonFile"
```

---

## Banco de dados

### Tabela `customers`

Armazena os cadastros realizados.

```txt
id
nome
email
cep
logradouro
numero
complemento
bairro
cidade
uf
created_at
updated_at
```

### Tabela `cep_caches`

Armazena CEPs já consultados para evitar chamadas repetidas à API externa.

```txt
id
cep
logradouro
bairro
cidade
uf
raw_response
created_at
updated_at
```

### Tabela `cache`

Tabela usada pelo Laravel para cache de aplicação.

No provider Correios, essa tabela pode armazenar o token temporariamente. No fluxo de CEP, o cache de endereços fica na tabela `cep_caches`.

---

## Testes automatizados

Rodar todos os testes do backend:

```bash
docker compose exec api php artisan test
```

Resultado validado:

```txt
Tests: 22 passed (105 assertions)
```

Os testes cobrem:

- busca de CEP usando ViaCEP com `Http::fake()`;
- cache de CEP;
- retorno 422 para CEP inválido;
- busca com provider fake;
- retorno 404 para CEP inexistente no provider fake;
- geração simulada de token dos Correios;
- consulta simulada na API Busca CEP dos Correios;
- uso de Bearer Token fixo sem chamar endpoint de token;
- criação de cadastro;
- validação do cadastro;
- e-mail duplicado;
- listagem de cadastros;
- atualização completa com `PUT`;
- atualização parcial com `PATCH`;
- exclusão com `DELETE`.

---

## Build do frontend

Dentro da pasta `frontend`:

```bash
npm run build
```

Resultado validado:

```txt
vite building client environment for production...
✓ built
```

O build gera os arquivos finais em:

```txt
frontend/dist/
```

---

## Comandos úteis

Subir ambiente backend:

```bash
docker compose up -d --build
```

Parar containers:

```bash
docker compose down
```

Resetar ambiente e apagar volumes:

```bash
docker compose down -v
docker compose up -d --build
```

Limpar cache Laravel:

```bash
docker compose exec api php artisan optimize:clear
```

Rodar migrations:

```bash
docker compose exec api php artisan migrate
```

Rodar testes:

```bash
docker compose exec api php artisan test
```

Entrar no container da API:

```bash
docker compose exec api sh
```

Acessar o MySQL:

```bash
docker compose exec mysql mysql -ucep_user -pcep_password cep_clientes
```

Consultar cache de CEP:

```bash
docker compose exec mysql mysql -ucep_user -pcep_password cep_clientes -e "SELECT cep, logradouro, bairro, cidade, uf FROM cep_caches;"
```

Consultar clientes:

```bash
docker compose exec mysql mysql -ucep_user -pcep_password cep_clientes -e "SELECT id, nome, email, cep, cidade, uf FROM customers;"
```

Rodar frontend em desenvolvimento:

```bash
cd frontend
npm run dev
```

Buildar frontend:

```bash
cd frontend
npm run build
```

---

## Checklist dos requisitos

| Requisito | Status |
|---|---:|
| Backend em PHP | OK |
| Laravel | OK |
| API REST | OK |
| Endpoint de busca de CEP | OK |
| Retorno formatado de endereço | OK |
| Integração externa real | OK, via ViaCEP |
| Provider Correios preparado | OK |
| Endpoint de cadastro | OK |
| Endpoint de listagem | OK |
| Persistência em banco | OK |
| Frontend React | OK |
| Frontend TypeScript | OK |
| Interface em tela única | OK |
| Busca automática do CEP no frontend | OK |
| Preenchimento automático de endereço | OK |
| Listagem visual dos registros | OK |
| Edição de cliente | OK, diferencial |
| Exclusão de cliente | OK, diferencial |
| Validações | OK |
| Tratamento de erros | OK |
| Cache de CEP | OK |
| Testes automatizados | OK |
| Docker/docker-compose | OK |
| Migrations | OK |
| Variáveis de ambiente documentadas | OK |
| README com instruções | OK |

---

## Sobre a decisão ViaCEP + Correios

O requisito original do teste cita a API dos Correios. Durante a implementação, a API oficial dos Correios exigiu liberação específica da API Busca CEP no contrato. Como o token era aceito, mas o endpoint de CEP não estava autorizado, a aplicação foi ajustada para usar uma estratégia de providers.

Com isso:

- o backend segue funcional e testável localmente com ViaCEP;
- o provider Correios continua implementado;
- a troca de provider é feita por variável de ambiente;
- os testes não dependem da disponibilidade de serviços externos;
- o código fica preparado para ativar Correios quando a liberação contratual estiver disponível.

Essa decisão evita deixar o projeto quebrado por uma restrição externa de credencial/contrato e demonstra separação de responsabilidades.

---

## Observações para defesa técnica

Pontos importantes para explicar em entrevista:

1. A consulta de CEP não está no controller; fica isolada em serviços.
2. O controller recebe a requisição e devolve resposta padronizada.
3. A escolha do provider é feita via configuração.
4. O frontend não precisa saber se o CEP veio de ViaCEP, Correios ou cache.
5. O cache evita chamadas externas repetidas.
6. As credenciais ficam no `.env`.
7. O provider fake existe apenas para testes.
8. Os testes usam `Http::fake()` para não depender de rede externa.
9. A API dos Correios foi mantida como provider ativável, apesar da restrição contratual no ambiente atual.
10. O frontend foi mantido em tela única para reduzir complexidade e melhorar UX.
11. Modais foram usados apenas para edição, confirmação e feedback.
12. O projeto pode evoluir para autenticação, paginação, CI/CD e deploy sem reescrever o fluxo principal.

---

## Troubleshooting

### A porta 8081 já está em uso

Altere a porta no `docker-compose.yml` ou pare o serviço que está usando a porta.

### O frontend não consegue acessar a API

Confira:

```env
VITE_API_BASE_URL=http://localhost:8081/api
```

Confira também o CORS no backend:

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Depois limpe o cache:

```bash
docker compose exec api php artisan optimize:clear
```

### Erro de acentos no terminal MySQL

Se aparecer algo como `Her�clito` no terminal MySQL, é apenas problema de exibição/codificação do terminal. A API retorna os acentos corretamente em JSON.

### Correios retorna acesso não autorizado

Isso indica que as credenciais/token podem estar válidos, mas o contrato não possui a API Busca CEP liberada.

Nesse caso, use localmente:

```env
CEP_PROVIDER=viacep
```

E mantenha o provider dos Correios para uso futuro quando a API estiver liberada.

---

## Próximas melhorias possíveis

- paginação na listagem de clientes;
- autenticação com Laravel Sanctum;
- rate limit específico para `/api/ceps/{cep}`;
- CI com GitHub Actions;
- testes E2E no frontend;
- Docker também para o frontend;
- deploy em VPS ou cloud;
- coleção Postman/Insomnia versionada.

---

## Licença

Projeto desenvolvido para teste técnico.
