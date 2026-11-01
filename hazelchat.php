<?php

/*
 * Hazelchat for Grav: adds the Hazelchat AI chat agent to every page of a
 * Grav site from one installation code.
 *
 * (C) 2026 Hazelchat. MIT License; see LICENSE.
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Hazelchat\Key;
use RocketTheme\Toolbox\Event\Event;

require_once __DIR__ . '/classes/Key.php';

class HazelchatPlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /** Public pages only: the admin never loads the chat. */
    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }
        $this->enable([
            'onOutputGenerated' => ['onOutputGenerated', 0],
            // Fired only by Grav's Consent plugin (Grav 2), when installed.
            'onConsentRegisterServices' => ['onConsentRegisterServices', 0],
        ]);
    }

    /** The tag, just before </body>, on HTML pages only. */
    public function onOutputGenerated(): void
    {
        if (!$this->isHtmlPage()) {
            return;
        }
        $settings = $this->settings();
        $this->grav->output = Key::insert((string) $this->grav->output, Key::tag($settings['key'], $settings['consent']));
    }

    /** Lists the chat on Grav's consent banner (see Key::consentService). */
    public function onConsentRegisterServices(Event $event): void
    {
        $settings = $this->settings();
        $service = Key::consentService($settings['key'], $settings['consent']);
        if ($service !== null && isset($event['services'])) {
            $event['services']->add('hazelchat', $service + ['owner' => 'hazelchat']);
        }
    }

    /** @return array{key: ?string, consent: bool} */
    private function settings(): array
    {
        return [
            'key' => Key::fromInput($this->config->get('plugins.hazelchat.code')),
            'consent' => (bool) $this->config->get('plugins.hazelchat.consent', false),
        ];
    }

    /** JSON, RSS, XML and other page formats stay as they are. */
    private function isHtmlPage(): bool
    {
        $page = $this->grav['page'] ?? null;
        if ($page === null || !method_exists($page, 'templateFormat')) {
            return false;
        }

        return $page->templateFormat() === 'html';
    }
}
