# Hazelchat for Grav

Adds your [Hazelchat](https://hazelchat.co/?utm_source=grav&utm_medium=plugin&utm_campaign=readme) AI chat agent to every page of a Grav site from one pasted installation code. The agent answers visitors from the pages you approved, shows its sources and passes on their enquiries.

- Grav 1.7 and Grav 2, with the classic admin or Admin Next, or without an admin.
- Needs a Hazelchat account. The Free plan covers one website and stays free.

## Install

1. In Hazelchat, add your website and copy its installation code ([create a free account](https://app.hazelchat.co/signup?utm_source=grav&utm_medium=plugin&utm_campaign=readme-signup)).
2. Install the plugin: in the admin, add the **Hazelchat** plugin, or run `bin/gpm install hazelchat`.
3. Open the plugin's settings, paste the installation code and save. Without an admin, put it in `user/config/plugins/hazelchat.yaml`:

   ```yaml
   enabled: true
   code: '<your installation code or its key>'
   ```
4. Back in Hazelchat, click **Publish and switch on**. Hazelchat finds the chat on your live page and verifies the website.

## What it does

- Every HTML page gets one tag just before `</body>`:
  `<script src="https://app.hazelchat.co/widget.js" id="hazelchat-grav" defer data-agent="<key>"></script>`
- The installation code may be the pasted code or just its 64-character key. Only a valid key is printed; anything else, such as an API key, prints nothing. The script address is fixed and cannot be changed in the settings.
- **Wait for my consent manager** (off) adds `data-require-consent="true"`: the chat starts only once your consent manager allows it. The script file itself still loads straight away; see [Data and third parties](#data-and-third-parties) and [Visitor consent](https://hazelchat.co/integrations/grav/#consent).
- The admin never loads the chat, and JSON, RSS and other page formats stay as they are.
- Admin texts in English and German.

### With Grav's Consent plugin (Grav 2)

If [Consent](https://github.com/getgrav/grav-plugin-consent) is installed, Hazelchat appears on its banner and in its inventory without any setup:

- Switch off: under **Necessary**. The chat sets no cookies; it keeps only a reference to the conversation for the open tab. Whether that label fits your site is your decision. If your site follows strict privacy rules, turn the switch on.
- Switch on: under **Functional**. The chat starts once the visitor allows that category, without a page reload, and forgets the conversation if the visitor withdraws it.

Consent asks visitors again once after a plugin adds something to its inventory (its `auto_version` setting), so expect one new question after installing Hazelchat.

## Data and third parties

- Visitors' browsers load the chat from `app.hazelchat.co` (Hazelchat, Germany). Hazelchat processes the questions they type and, if they leave an enquiry, their name, email address and message ([privacy notice](https://hazelchat.co/privacy/), [terms](https://hazelchat.co/terms/)).
- With **Wait for my consent manager** on, only the chat waits. Browsers still download `widget.js` from `app.hazelchat.co` straight away, so the visitor's IP address reaches Hazelchat before they agree. This is by design. If your site must prevent that too, use a consent tool that can block the script itself.
- The script tag carries the id `hazelchat-grav`. When Hazelchat checks your website, it reads this id to record that the chat was installed with this plugin, and it counts the plugin's installations that way.
- The chat sets no cookies. It keeps a reference to the conversation in session storage until the tab is closed (details on the [cookie banner page](https://hazelchat.co/integrations/cookie-banners/)).
- The plugin itself makes no requests from Grav, adds no database tables or files, and stores only the installation code and the switch in `user/config/plugins/hazelchat.yaml`.

## Remove

Clear the installation code in the plugin's settings, disable the plugin, or uninstall it (`bin/gpm uninstall hazelchat`). Your Hazelchat account and plan are not affected.

## Deutsch

Hazelchat fügt Ihren KI-Chat-Agenten mit einem einzigen Installationscode in jede Seite Ihrer Grav-Site ein (Grav 1.7 und 2). Installieren Sie das Plugin im Admin oder mit `bin/gpm install hazelchat`, öffnen Sie seine Einstellungen, fügen Sie den Code aus Hazelchat ein und speichern Sie. Die Browser Ihrer Besucher laden den Chat von `app.hazelchat.co`; deren Fragen und Anfragedaten verarbeitet Hazelchat. Ist Gravs Consent-Plugin installiert, erscheint Hazelchat ohne Einrichtung in dessen Banner. Auch mit „Auf meinen Consent-Manager warten“ lädt der Browser `widget.js` sofort, und die IP-Adresse des Besuchers erreicht Hazelchat schon vor der Einwilligung; nur der Chat wartet. Über die ID `hazelchat-grav` im Skript-Tag zählt Hazelchat die Installationen dieses Plugins. Sie brauchen ein Hazelchat-Konto; der Free-Tarif gilt für eine Website und bleibt kostenlos.

## Licence

MIT. Grav is a trademark of its makers; Hazelchat is not affiliated with or endorsed by them.
