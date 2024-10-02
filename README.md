# Trello PHP Class

Esta classe PHP fornece uma interface para interagir com a API do Trello. Permite gerenciar cartões, listas e quadros de forma programática, facilitando a automação e integração com aplicativos externos.

## Requisitos

- PHP 7.0 ou superior
- Extensão cURL habilitada

## Instalação

Para usar esta classe, você pode incluí-la em seu projeto PHP e criar uma instância da classe `Trello`. Certifique-se de definir as constantes `TRELLO_KEY`, `TRELLO_TOKEN` e `TRELLO_ENDPOINT` com seus valores específicos.

```php
define('TRELLO_KEY', 'sua_chave_trello');
define('TRELLO_TOKEN', 'seu_token_trello');
define('TRELLO_ENDPOINT', 'https://api.trello.com/1');
