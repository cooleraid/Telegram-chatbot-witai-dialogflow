# Telegram-chatbot-witai-dialogflow
Chatbot with Telegram, Wit ai, Dialogflow and CodeIgniter

## Requirements
1. [Wit AI](https://wit.ai/) Account.
2. [Dialogflow](https://dialogflow.com/) Account.
3. [Telegram](https://telegram.org/) Account.
4. Linux OS (feel free to use any OS).
5. Gcloud.
6. [Git](https://git-scm.com/).
7. [PHP](https://www.php.net/).
8. [Ngrok](https://ngrok.com/).

## Setup
#### Telegram:
1. Create a new chatbot via [BotFather](https://telegram.me/BotFather).
2. Store the newly created telegram bot access token in your OS environment variable by running:

```bash
export TELEGRAM_ACCESS_TOKEN=YOUR-ACCESS-TOKEN
```

#### DialogFlow:
1. Enable Small Talks in your Dialogflow account.
2. Store your Dialogflow project ID in your OS environment variable by running:
```bash
export DIALOGFLOW_PROJECT_ID=YOUR-DIALOGFLOW-PROJECT-ID

```

3. Export your private key for Dialogflow integrations in JSON format
4. Store the path to your JSON file in your OS environment variable by running:


```bash
export GOOGLE_APPLICATION_CREDENTIALS="/home/user/Downloads/ordertracker-isocuo-d7882fd63303.json"
```
#### Wit AI:
1. Import the project [Wit AI data](https://github.com/cooleraid/Telegram-chatbot-witai-dialogflow/blob/master/witai_data/MyFirstApp-2019-10-03-13-56-41.zip) into a new app on wit.ai.
2. Store the wit.ai server access token in your OS environment variable by running:

```bash
export WITAI_ACCESS_TOKEN=YOUR-WITAI-ACCESS-TOKEN
```

## Usage
Navigate to the project directory in your terminal and start your PHP local web server

```bash
php -S localhost:8000
```
Open a new terminal, Navigate to the project directory and start your Ngrok Server

```bash
ngrok http 8000
```
To set the webhook URL to your chatbot, run this in your terminal. 
Note: Replace YOUR_NGROK_URL with your Ngrok server URL.

```bash
curl https://api.telegram.org/bot$TELEGRAM_ACCESS_TOKEN/setWebhook?url=https://YOUR_NGROK_URL/index.php/flow/webhook
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](https://github.com/cooleraid/Telegram-chatbot-witai-dialogflow/blob/master/LICENSE)
