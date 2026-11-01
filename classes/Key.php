<?php

/*
 * This file is part of Hazelchat for Grav.
 *
 * (C) 2026 Hazelchat. MIT License; see LICENSE.
 */

namespace Grav\Plugin\Hazelchat;

/**
 * The public widget key and the one script tag, as plain functions with no
 * Grav dependency, so they can be tested with PHP alone. The key is public:
 * it names an agent and grants no access to the Hazelchat account. Written
 * for PHP 7.3, the oldest PHP Grav 1.7 runs on.
 */
final class Key
{
    /** The only address this plugin ever prints. */
    const SCRIPT_URL = 'https://app.hazelchat.co/widget.js';

    /** Hazelchat counts installations of this plugin by this id. */
    const SCRIPT_ID = 'hazelchat-grav';

    /** Room for a pasted installation code; the key itself is 64 characters. */
    const MAX_INPUT = 1000;

    /** @param mixed $key */
    public static function valid($key): bool
    {
        return \is_string($key) && preg_match('/\A[a-f0-9]{64}\z/', $key) === 1;
    }

    /**
     * The setting takes the whole installation code or just the key. A bare
     * 64-hex value is the key; otherwise the text must hold exactly one
     * data-agent attribute (outside HTML comments) whose value is 64
     * lowercase hex. Anything else gives null, and nothing is printed.
     *
     * @param mixed $value
     */
    public static function fromInput($value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || \strlen($value) > self::MAX_INPUT) {
            return null;
        }
        if (self::valid($value)) {
            return $value;
        }

        // A key that is only mentioned in a comment is not the code.
        $value = preg_replace('/<!--.*?-->/s', '', $value);
        if ($value === null || strpos($value, '<!--') !== false) {
            return null;
        }

        // Exactly one data-agent attribute; look-alikes such as
        // xdata-agent or data-agent-x are not it, and two are ambiguous.
        $attribute = '(?<![A-Za-z0-9_:-])data-agent(?![A-Za-z0-9_:-])\s*=';
        if (preg_match_all('/' . $attribute . '/i', $value) !== 1) {
            return null;
        }
        if (preg_match('/' . $attribute . '\s*(?:"([a-f0-9]{64})"|\'([a-f0-9]{64})\'|([a-f0-9]{64})(?=[\s>\/]|\z))/', $value, $match) !== 1) {
            return null;
        }

        $key = ($match[1] ?? '') . ($match[2] ?? '') . ($match[3] ?? '');

        return self::valid($key) ? $key : null;
    }

    /**
     * The script tag, or null when nothing may be printed. No inline code,
     * no data-platform and no address but SCRIPT_URL.
     */
    public static function tag(?string $key, bool $requireConsent): ?string
    {
        if (!self::valid($key)) {
            return null;
        }

        return '<script src="' . self::SCRIPT_URL . '" id="' . self::SCRIPT_ID . '" defer data-agent="' . $key . '"'
            . ($requireConsent ? ' data-require-consent="true"' : '')
            . '></script>';
    }

    /**
     * Puts the tag just before the page's last </body>. A page without
     * </body> (a fragment) or one that already carries the tag stays as it
     * is. Only a real </body> counts: </bodyx> does not.
     */
    public static function insert(string $html, ?string $tag): string
    {
        if ($tag === null || preg_match('/\bid\s*=\s*["\']?' . self::SCRIPT_ID . '["\'\s>]/i', $html) === 1) {
            return $html;
        }
        if (preg_match_all('/<\/body\s*>/i', $html, $found, PREG_OFFSET_CAPTURE) < 1) {
            return $html;
        }
        $at = $found[0][\count($found[0]) - 1][1];

        return substr($html, 0, $at) . $tag . "\n" . substr($html, $at);
    }

    /**
     * What Grav's Consent plugin (Grav 2) needs to list the chat on its
     * banner, or null when no tag is printed. Without the consent switch the
     * chat is strictly necessary: it sets no cookies and keeps only a
     * conversation reference for the open tab. With the switch, it is
     * functional, and Consent runs one line that lets the waiting chat start
     * once the visitor allows that category (after the deferred chat script
     * has run, so the signal is not lost), and tells it about a later
     * withdrawal so it forgets the conversation.
     *
     * @return array<string, mixed>|null
     */
    public static function consentService(?string $key, bool $requireConsent): ?array
    {
        if (!self::valid($key)) {
            return null;
        }
        $service = [
            'category' => $requireConsent ? 'functional' : 'necessary',
            'name' => 'Hazelchat',
            'description' => 'PLUGIN_HAZELCHAT.CONSENT.DESCRIPTION',
            'provider' => 'Hazelchat (app.hazelchat.co)',
            'privacy_url' => 'https://hazelchat.co/privacy/',
            'cookies' => [
                ['name' => 'hazelchat-chat:' . $key, 'duration' => 'PLUGIN_HAZELCHAT.CONSENT.UNTIL_TAB_CLOSED', 'type' => 'session_storage', 'description' => 'PLUGIN_HAZELCHAT.CONSENT.CHAT'],
                ['name' => 'hazelchat-visited', 'duration' => 'PLUGIN_HAZELCHAT.CONSENT.UNTIL_TAB_CLOSED', 'type' => 'session_storage', 'description' => 'PLUGIN_HAZELCHAT.CONSENT.OPENER'],
                ['name' => 'hazelchat-opener-shown', 'duration' => 'PLUGIN_HAZELCHAT.CONSENT.UNTIL_TAB_CLOSED', 'type' => 'session_storage', 'description' => 'PLUGIN_HAZELCHAT.CONSENT.OPENER'],
            ],
        ];
        if ($requireConsent) {
            $service['cookies'][] = ['name' => 'hazelchat-forget', 'duration' => 'PLUGIN_HAZELCHAT.CONSENT.UNTIL_TAB_CLOSED', 'type' => 'session_storage', 'description' => 'PLUGIN_HAZELCHAT.CONSENT.FORGET'];
            $service['scripts'] = [['inline' => self::CONSENT_SIGNAL]];
        }

        return $service;
    }

    /**
     * The one line Consent runs when the visitor allows the chat. Consent's
     * own script is deferred like the chat's and may run first, so the
     * signal waits for DOMContentLoaded unless the chat script has already
     * run (it marks itself on window as it starts listening).
     */
    const CONSENT_SIGNAL = "(function(){function s(v){window.dispatchEvent(new CustomEvent('hazelchat-consent',{detail:v}))}"
        . "document.addEventListener('consent:changed',function(e){var d=e.detail&&e.detail.denied;if(d&&d.indexOf('functional')>-1)s(false)});"
        . "if(window[Symbol.for('hazelchat.loader')])s(true);else document.addEventListener('DOMContentLoaded',function(){s(true)})})();";
}
