# Integração Asaas - Versão 1.0

**Projeto:** IESB - Inteligência Educacional Souza Brazil  
**Arquitetura:** PHP MVC + MySQL + Asaas API + Webhooks

---

# Objetivo

Implementar a primeira versão da integração com o Asaas para que:

1. O aluno realize uma pré-inscrição no site;
2. O sistema gere automaticamente a cobrança via API do Asaas;
3. O aluno efetue o pagamento;
4. O Asaas envie um Webhook;
5. O sistema crie automaticamente o cadastro do aluno;
6. O sistema matricule o aluno na turma escolhida;
7. A pré-inscrição permaneça como histórico financeiro da operação.

---

# Conceito da Arquitetura

Existem dois domínios independentes:

## Comercial

Responsável por:

- Pré-inscrição
- Cobrança
- Integração com Asaas
- Status financeiro

Tabela principal:

```
pre_inscricoes
```

---

## Acadêmico

Responsável por:

- Alunos
- Turmas
- Matrículas

Tabelas:

```
alunos

turmas

matriculas
```

---

O Webhook será o responsável por ligar os dois mundos.

```
Pré-inscrição

↓

Pagamento confirmado

↓

Aluno

↓

Matrícula
```

---

# Fluxo Geral

```
Aluno

↓

Seleciona Curso

↓

Seleciona Turma

↓

Preenche formulário

↓

Grava Pré-inscrição

↓

Cria Customer Asaas

↓

Cria Cobrança

↓

Salva

asaas_customer

asaas_payment

invoice_url

↓

Aluno realiza pagamento

↓

Webhook PAYMENT_RECEIVED

↓

Processamento

↓

Cria aluno

↓

Cria matrícula

↓

Atualiza pré-inscrição
```

---

# Estrutura da tabela

## pre_inscricoes

```sql
id

id_curso

id_turma

id_forma_pagamento

nome

cpf

email

telefone

valor

asaas_customer

asaas_payment

invoice_url

status

id_aluno

id_matricula

processado_em

created_at

updated_at
```

---

## Campo status

Estados possíveis:

```
PENDENTE

AGUARDANDO

PROCESSANDO

MATRICULADO

ERRO

CANCELADO
```

Descrição:

### PENDENTE

Pré-inscrição criada.

Ainda não foi enviada ao Asaas.

---

### AGUARDANDO

Cobrança criada.

Aguardando pagamento.

---

### PROCESSANDO

Webhook recebido.

Sistema criando aluno e matrícula.

---

### MATRICULADO

Processamento concluído.

Aluno matriculado.

---

### ERRO

Erro durante processamento.

Necessário verificar.

---

### CANCELADO

Pagamento cancelado.

---

# Turmas

Cada inscrição deve possuir obrigatoriamente um ID da turma.

Não utilizar lógica de:

```
Buscar turma ativa.
```

A decisão da turma acontece no momento da inscrição.

Exemplo:

```
Curso

Direito Tributário

↓

Turma Presencial

id_turma = 15

↓

Gravar

id_turma = 15
```

Mesmo que a turma seja encerrada posteriormente,
o aluno continuará pertencendo à turma originalmente escolhida.

---

# Admin

Ao cadastrar um curso, o administrador deverá informar:

- Curso
- Turma
- Modalidade
- Data de início
- Data de término
- Ativa (Sim/Não)

O front-end utilizará sempre a turma cadastrada.

---

# Estrutura MVC

Criar:

```
app/

Controllers/

WebhookController.php
```

---

Criar:

```
Services/

AsaasService.php

AsaasWebhookService.php

MatriculaService.php

AlunoService.php

TurmaService.php
```

---

Repositories

```
AlunoRepository.php

InscricaoRepository.php

MatriculaRepository.php

TurmaRepository.php
```

---

# Rota

Adicionar rota POST

```
POST /webhook/asaas
```

URL utilizada no painel Asaas:

```
https://SEU_DOMINIO/webhook/asaas
```

Não utilizar páginas administrativas.

O endpoint deve ser público.

---

# Webhook

Receber apenas POST.

Ler:

```
php://input
```

Converter JSON.

Validar Token.

Retornar HTTP 200.

---

# Eventos utilizados

Versão inicial:

```
PAYMENT_RECEIVED

PAYMENT_UPDATED
```

---

## PAYMENT_RECEIVED

Responsável por gerar matrícula.

Fluxo:

```
Recebe evento

↓

Localiza pré-inscrição

↓

Verifica se já foi processada

↓

BEGIN TRANSACTION

↓

Localiza aluno por CPF

↓

Existe?

Sim

↓

Usa mesmo ID

Não

↓

Cria aluno

↓

Obtém id_aluno

↓

Cria matrícula

↓

Atualiza pré-inscrição

status = MATRICULADO

id_aluno

id_matricula

processado_em

↓

COMMIT

↓

HTTP 200
```

---

## PAYMENT_UPDATED

Não cria matrícula.

Somente sincroniza informações da cobrança.

Exemplo:

- valor
- vencimento
- invoice_url
- informações financeiras

---

# Localização da inscrição

Sempre localizar utilizando:

```
asaas_payment
```

Nunca utilizar status.

Exemplo:

```sql
SELECT *

FROM pre_inscricoes

WHERE asaas_payment = ?
```

---

# Idempotência

O Asaas pode reenviar Webhooks.

Antes de qualquer processamento verificar:

```
id_matricula
```

ou

```
status == MATRICULADO
```

Caso já exista:

```
Retornar HTTP 200

Encerrar processamento
```

Nunca criar matrícula duplicada.

---

# Transação

Todo processamento deverá ocorrer dentro de uma transação.

```
BEGIN

↓

Criar aluno

↓

Criar matrícula

↓

Atualizar pré-inscrição

↓

COMMIT
```

Em caso de erro:

```
ROLLBACK
```

---

# Logs

Criar diretório:

```
storage/

logs/

asaas/
```

Registrar:

- Data/Hora
- Evento
- Payment ID
- Payload recebido
- Resultado
- Mensagem de erro

---

# Segurança

Validar Token do Webhook.

Armazenar no:

```
.env
```

Exemplo:

```
ASAAS_API_KEY=xxxxxxxxxxxxxxxx

ASAAS_WEBHOOK_TOKEN=xxxxxxxxxxxxxxxx
```

Nunca processar Webhooks sem validar o token.

---

# Responsabilidade das Classes

## AsaasService

Responsável por:

- Customer
- Cobranças
- Consultas

Não cria matrícula.

---

## WebhookController

Responsável apenas por receber o POST.

Nenhuma regra de negócio.

---

## AsaasWebhookService

Responsável por:

- Validar Token
- Interpretar Evento
- Encaminhar processamento

---

## MatriculaService

Responsável por:

- Criar aluno
- Criar matrícula
- Atualizar pré-inscrição

Toda regra de negócio deverá permanecer nesta classe.

---

## Repositories

Cada Repository deverá manipular apenas sua tabela.

Nunca colocar regra de negócio dentro dos Repositories.

---

# Objetivo da Versão 1

Implementar um fluxo completamente automático:

```
Pré-inscrição

↓

Cobrança Asaas

↓

Pagamento

↓

Webhook

↓

Aluno

↓

Matrícula

↓

Histórico atualizado
```

---

# Preparação para versões futuras

A arquitetura deverá permitir posteriormente:

- Integração com Moodle
- Geração automática de usuário Moodle
- Matrícula automática no curso Moodle
- Envio de e-mails de boas-vindas
- Geração de contrato
- Área do aluno
- Emissão de boletos posteriores
- Renovação de matrícula

Sem necessidade de alterar o fluxo principal implementado nesta versão.
