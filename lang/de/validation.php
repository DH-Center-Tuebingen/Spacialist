<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => ':Attribute muss akzeptiert werden.',
    'active_url'           => ':Attribute ist keine gültige Internet-Adresse.',
    'after'                => ':Attribute muss ein Datum nach dem :date sein.',
    'after_or_equal'       => ':Attribute muss ein Datum nach dem :date oder gleich dem :date sein.',
    'alpha'                => ':Attribute darf nur aus Buchstaben bestehen.',
    'alpha_dash'           => ':Attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.',
    'alpha_num'            => ':Attribute darf nur aus Buchstaben und Zahlen bestehen.',
    'array'                => ':Attribute muss ein Array sein.',
    'before'               => ':Attribute muss ein Datum vor dem :date sein.',
    'before_or_equal'      => ':Attribute muss ein Datum vor dem :date oder gleich dem :date sein.',
    'between'              => [
        'numeric' => ':Attribute muss zwischen :min & :max liegen.',
        'file'    => ':Attribute muss zwischen :min & :max Kilobytes groß sein.',
        'string'  => ':Attribute muss zwischen :min & :max Zeichen lang sein.',
        'array'   => ':Attribute muss zwischen :min & :max Elemente haben.',
    ],
    'boolean'              => ":attribute muss entweder 'true' oder 'false' sein.",
    'confirmed'            => ':Attribute stimmt nicht mit der Bestätigung überein.',
    'date'                 => ':Attribute muss ein gültiges Datum sein.',
    'date_format'          => ':Attribute entspricht nicht dem gültigen Format für :format.',
    'definition'           => 'Der Wert erfüllt nicht die Vorgabe für :type.',
    'different'            => ':Attribute und :other müssen sich unterscheiden.',
    'digits'               => ':Attribute muss :digits Stellen haben.',
    'digits_between'       => ':Attribute muss zwischen :min und :max Stellen haben.',
    'dimensions'           => ':Attribute hat ungültige Bildabmessungen.',
    'distinct'             => ':Attribute beinhaltet einen bereits vorhandenen Wert.',
    'email'                => ':Attribute muss eine gültige E-Mail-Adresse sein.',
    'exists'               => 'Der gewählte Wert für :attribute ist ungültig.',
    'file'                 => ':Attribute muss eine Datei sein.',
    'filled'               => ':Attribute muss ausgefüllt sein.',
    'gt'                   => [
        'numeric' => ':Attribute muss mindestens :value sein.',
        'file'    => ':Attribute muss mindestens :value Kilobytes groß sein.',
        'string'  => ':Attribute muss mindestens :value Zeichen lang sein.',
        'array'   => ':Attribute muss mindestens :value Elemente haben.',
    ],
    'gte'                  => [
        'numeric' => ':Attribute muss größer oder gleich :value sein.',
        'file'    => ':Attribute muss größer oder gleich :value Kilobytes sein.',
        'string'  => ':Attribute muss größer oder gleich :value Zeichen lang sein.',
        'array'   => ':Attribute muss größer oder gleich :value Elemente haben.',
    ],
    'image'                => ':Attribute muss ein Bild sein.',
    'import_format'        => 'Übergebene Daten haben das falsche Format, erwartet war: :format.',
    'import_not_supported' => 'Import wird nicht unterstützt.',
    'in'                   => 'Der gewählte Wert für :attribute ist ungültig.',
    'in_array'             => 'Der gewählte Wert für :attribute kommt nicht in :other vor.',
    'integer'              => ':Attribute muss eine ganze Zahl sein.',
    'integer_positive'     => ':Attribute muss eine positive Ganzzahl sein.',
    'invalid_geodata'      => 'Geodaten sind inkorrekt.',
    'ip'                   => ':Attribute muss eine gültige IP-Adresse sein.',
    'ipv4'                 => ':Attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6'                 => ':Attribute muss eine gültige IPv6-Adresse sein.',
    'json'                 => ':Attribute muss ein gültiger JSON-String sein.',
    'lt'                   => [
        'numeric' => ':Attribute muss kleiner :value sein.',
        'file'    => ':Attribute muss kleiner :value Kilobytes groß sein.',
        'string'  => ':Attribute muss kleiner :value Zeichen lang sein.',
        'array'   => ':Attribute muss kleiner :value Elemente haben.',
    ],
    'lte'                  => [
        'numeric' => ':Attribute muss kleiner oder gleich :value sein.',
        'file'    => ':Attribute muss kleiner oder gleich :value Kilobytes sein.',
        'string'  => ':Attribute muss kleiner oder gleich :value Zeichen lang sein.',
        'array'   => ':Attribute muss kleiner oder gleich :value Elemente haben.',
    ],
    'max'                  => [
        'numeric' => ':Attribute darf maximal :max sein.',
        'file'    => ':Attribute darf maximal :max Kilobytes groß sein.',
        'string'  => ':Attribute darf maximal :max Zeichen haben.',
        'array'   => ':Attribute darf nicht mehr als :max Elemente haben.',
    ],
    'mimes'                => ':Attribute muss den Dateityp :values haben.',
    'mimetypes'            => ':Attribute muss den Dateityp :values haben.',
    'min'                  => [
        'numeric' => ':Attribute muss mindestens :min sein.',
        'file'    => ':Attribute muss mindestens :min Kilobytes groß sein.',
        'string'  => ':Attribute muss mindestens :min Zeichen lang sein.',
        'array'   => ':Attribute muss mindestens :min Elemente haben.',
    ],
    'not_in'               => 'Der gewählte Wert für :attribute ist ungültig.',
    'not_regex'            => ':Attribute hat ein ungültiges Format.',
    'numeric'              => ':Attribute muss eine Zahl sein.',
    'object_missing'       => ':Object existiert nicht.',
    'present'              => ':Attribute muss vorhanden sein.',
    'regex'                => ':Attribute Format ist ungültig.',
    'required'             => ':Attribute muss ausgefüllt sein.',
    'required_if'          => ':Attribute muss ausgefüllt sein, wenn :other :value ist.',
    'required_unless'      => ':Attribute muss ausgefüllt sein, wenn :other nicht :values ist.',
    'required_with'        => ':Attribute muss angegeben werden, wenn :values ausgefüllt wurde.',
    'required_with_all'    => ':Attribute muss angegeben werden, wenn :values ausgefüllt wurde.',
    'required_without'     => ':Attribute muss angegeben werden, wenn :values nicht ausgefüllt wurde.',
    'required_without_all' => ':Attribute muss angegeben werden, wenn keines der Felder :values ausgefüllt wurde.',
    'same'                 => ':Attribute und :other müssen übereinstimmen.',
    'size'                 => [
        'numeric' => ':Attribute muss gleich :size sein.',
        'file'    => ':Attribute muss :size Kilobyte groß sein.',
        'string'  => ':Attribute muss :size Zeichen lang sein.',
        'array'   => ':Attribute muss genau :size Elemente haben.',
    ],
    'string'               => ':Attribute muss ein String sein.',
    'timezone'             => ':Attribute muss eine gültige Zeitzone sein.',
    'types'                => 'Die Werte müssen vom Typ :type sein.',
    'unit'                 => ':Attribute muss eine gültige Einheit sein.',
    'unique'               => ':Attribute ist schon vergeben.',
    'uploaded'             => ':Attribute konnte nicht hochgeladen werden.',
    'url'                  => ':Attribute muss eine URL sein.',
    'uuid'                 => ':Attribute muss ein UUID sein.',
    'users'                => 'Benutzer wurden nicht gefunden.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name'                  => 'Name',
        'username'              => 'Benutzername',
        'email'                 => 'E-Mail-Adresse',
        'first_name'            => 'Vorname',
        'last_name'             => 'Nachname',
        'password'              => 'Passwort',
        'password_confirmation' => 'Passwort-Bestätigung',
        'city'                  => 'Stadt',
        'country'               => 'Land',
        'address'               => 'Adresse',
        'phone'                 => 'Telefonnummer',
        'mobile'                => 'Handynummer',
        'age'                   => 'Alter',
        'sex'                   => 'Geschlecht',
        'gender'                => 'Geschlecht',
        'day'                   => 'Tag',
        'month'                 => 'Monat',
        'year'                  => 'Jahr',
        'hour'                  => 'Stunde',
        'minute'                => 'Minute',
        'second'                => 'Sekunde',
        'title'                 => 'Titel',
        'content'               => 'Inhalt',
        'description'           => 'Beschreibung',
        'excerpt'               => 'Auszug',
        'date'                  => 'Datum',
        'time'                  => 'Uhrzeit',
        'available'             => 'verfügbar',
        'size'                  => 'Größe',
    ],
];
