<?php
class Config {
    // Database table names
    public const TABLE_LISTNAME  = "SMSGroups";
    public const TABLE_RECEIVERS = "SMSReceivers";
    public const TABLE_SENDSMS   = "SMSLog";

    // Database settings loaded from environment variables (.env)
    public static function dbHost(): string { return getenv('DB_HOST') ?: 'sms-tools-db'; }
    public static function dbUser(): string { return getenv('DB_USER') ?: ''; }
    public static function dbPass(): string { return getenv('DB_PASS') ?: ''; }
    public static function dbName(): string { return getenv('DB_NAME') ?: ''; }

    // GatewayAPI settings loaded from environment variables (.env)
    public static function apiUrl(): string   { return getenv('API_URL') ?: 'https://gatewayapi.com/rest/mtsms'; }
    public static function apiToken(): string { return getenv('API_TOKEN') ?: ''; }
}
