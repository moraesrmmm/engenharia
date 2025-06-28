# 📁 Estrutura do Painel Administrativo

## 🏗️ Nova Organização de Pastas

```
admin/
├── assets/                 # Recursos estáticos
│   ├── css/
│   │   └── admin.css      # Estilos específicos do admin
│   └── js/
│       └── admin.js       # Scripts específicos do admin
├── backend/               # Lógica de backend/processamento
│   ├── salvar_projeto.php
│   ├── toggle_destaque.php
│   └── excluir_projeto.php
├── includes/              # Componentes reutilizáveis
│   ├── header.php
│   └── footer.php
├── views/                 # Interface do usuário
│   ├── cadastrar_projeto.php
│   └── listar_projetos.php
├── auth.php              # Verificação de autenticação
├── criar_admin.php       # Script para criar usuário admin
├── dashboard.php         # Página principal do admin
├── login.php            # Página de login
└── gerar_projetos_exemplo.php  # Script para gerar dados de teste
```

## 🎯 Separação de Responsabilidades

### 📂 **backend/**
Contém toda a lógica de processamento do lado servidor:
- **salvar_projeto.php** - Processa cadastro de novos projetos
- **editar_projeto.php** - Processa edição de projetos existentes
- **toggle_destaque.php** - Alterna status de destaque dos projetos
- **excluir_projeto.php** - Remove projetos (soft delete)

### 📂 **views/**
Contém todas as interfaces de usuário:
- **cadastrar_projeto.php** - Formulário de cadastro de projetos
- **editar_projeto.php** - Formulário de edição de projetos
- **listar_projetos.php** - Listagem e gerenciamento de projetos

### 📂 **assets/**
Recursos estáticos organizados por tipo:
- **css/admin.css** - Estilos personalizados do admin
- **js/admin.js** - Funcionalidades JavaScript específicas

### 📂 **includes/**
Componentes reutilizáveis:
- **header.php** - Cabeçalho com navegação e estilos
- **footer.php** - Rodapé com scripts

## 🔄 Fluxo de Funcionamento

1. **Login** → `login.php`
2. **Dashboard** → `dashboard.php`
3. **Cadastrar** → `views/cadastrar_projeto.php` → `backend/salvar_projeto.php`
4. **Listar** → `views/listar_projetos.php`
5. **Toggle Destaque** → `backend/toggle_destaque.php`
6. **Excluir** → `backend/excluir_projeto.php`

## 🛡️ Segurança

- Todas as páginas verificam autenticação via `auth.php`
- Validação de dados no backend
- Transações de banco para consistência
- Soft delete para projetos (campo `ativo`)
- Upload seguro de imagens com validação

## 🎨 Features

- Interface responsiva com Bootstrap 5
- Validação em tempo real
- Preview de imagens (atual e nova na edição)
- Auto-save de rascunhos
- Feedback visual com mensagens
- Animações suaves
- Barra de progresso no formulário
- **Sistema completo de edição** - Editar projetos existentes com todos os dados
- **Gestão de cômodos** - Adicionar, remover e editar cômodos dinamicamente
- **Upload opcional** - Manter imagem atual ou substituir por nova
- **Validação robusta** - Client-side e server-side

## 🚀 Como usar

1. Execute o script: `setup_edicao.php` (configura tudo automaticamente)
2. Acesse: `login.php`
3. Use: `admin` / `123456`
4. Gerencie projetos pelo dashboard
5. **Edite projetos** via listagem → botão "Editar"

## � Fluxo de Edição

```
Dashboard → Listar Projetos → Selecionar Projeto → Editar → Salvar → Feedback
```

## �📱 Responsividade

- Mobile-first design
- Breakpoints otimizados
- Interface adaptável
- **Edição otimizada para mobile** - Interface touch-friendly
- Touch-friendly controls
