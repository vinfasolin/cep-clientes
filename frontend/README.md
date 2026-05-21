# CEP Clientes Frontend

Frontend em React + TypeScript + Vite para consumir a API Laravel do projeto CEP Clientes.

## Requisitos

- Node.js 20 ou superior
- Backend rodando em `http://localhost:8081`

## Instalação

Na raiz do projeto, entre na pasta do frontend:

```bash
cd frontend
```

Instale as dependências:

```bash
npm install
```

Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Confirme se o arquivo `.env` está assim:

```env
VITE_API_BASE_URL=http://localhost:8081/api
```

## Rodar em desenvolvimento

```bash
npm run dev
```

Acesse:

```txt
http://localhost:5173
```

## Build de produção

```bash
npm run build
```

## Funcionalidades

- Tela única responsiva.
- Cadastro de cliente.
- Busca automática de CEP ao digitar 8 números.
- Preenchimento automático de logradouro, bairro, cidade e UF.
- Listagem dos clientes cadastrados.
- Modal simples de sucesso e erro.
- Sem biblioteca pesada de UI.
