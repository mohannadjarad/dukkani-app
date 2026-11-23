<?php

namespace App\Console\Commands;

use App\Services\StoreProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateStoreCommand extends Command
{
    protected $signature = 'dukkani:store:create
                            {--store-name= : Store display name}
                            {--slug= : Public URL slug (lowercase Latin letters, numbers, dashes)}
                            {--owner-name= : Owner full name}
                            {--email= : Owner login email}
                            {--password= : Owner password (minimum 8 characters)}
                            {--phone= : Store phone, for example 0599123456}
                            {--whatsapp= : Store WhatsApp in international format, for example 970599123456}
                            {--city= : Store city}
                            {--currency= : Store currency code (defaults to the country currency)}
                            {--country-code=962 : Store country dial code, for example 962 or 970}
                            {--timezone= : Store timezone (defaults to the country timezone)}
                            {--no-trial : Skip the free 3-day Starter trial}';

    protected $description = 'Create a store with its owner account and default opening hours';

    public function handle(): int
    {
        $storeName = $this->optionOrAsk('store-name', 'Store name (as customers see it)');
        $slug = Str::slug($this->optionOrAsk('slug', 'Store URL slug (Latin letters and dashes, e.g. my-bakery)'));
        $ownerName = $this->optionOrAsk('owner-name', 'Owner full name');
        $email = Str::lower($this->optionOrAsk('email', 'Owner login email'));
        $password = (string) ($this->option('password') ?? '');
        $phone = $this->optionOrAsk('phone', 'Store phone, e.g. 0599123456');
        $whatsapp = $this->optionOrAsk('whatsapp', 'Store WhatsApp in international format, e.g. 970599123456');
        $city = $this->optionOrAsk('city', 'Store city', 'عمّان');
        $countryCode = $this->optionOrAsk('country-code', 'Store country dial code (e.g. 962 or 970)', '962');
        $country = (array) config("app.countries.{$countryCode}", []);
        $currency = Str::upper($this->optionOrAsk('currency', 'Store currency code', $country['currency'] ?? 'JOD'));
        $timezone = $this->optionOrAsk('timezone', 'Store timezone', $country['timezone'] ?? 'Asia/Amman');

        if ($password === '' && $this->input->isInteractive()) {
            $password = (string) $this->secret('Owner password (minimum 8 characters)');
        }

        $data = [
            'store_name' => $storeName,
            'slug' => $slug,
            'owner_name' => $ownerName,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'whatsapp' => $whatsapp ?: null,
            'city' => $city,
            'currency' => $currency,
            'country_code' => $countryCode,
            'timezone' => $timezone,
        ];

        $validator = Validator::make($data, [
            'store_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:stores,slug'],
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'country_code' => ['required', 'string', 'regex:/^[0-9]{1,4}$/'],
            'timezone' => ['required', 'timezone'],
        ], [
            'slug.regex' => 'The slug may only contain lowercase Latin letters, numbers, and dashes.',
            'slug.unique' => 'Another store already uses this slug.',
            'email.unique' => 'Another user already uses this email.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        [$user, $store] = app(StoreProvisioner::class)->create($data, ! $this->option('no-trial'));

        $this->newLine();
        $this->components->info('Store created successfully.');
        $this->table(['Field', 'Value'], [
            ['Store', $store->name],
            ['Store URL', url('/s/'.$store->slug)],
            ['Owner', $user->name],
            ['Login email', $user->email],
            ['Phone', $store->phone],
            ['Currency', $store->currency],
            ['Country code', $store->country_code],
            ['Plan', $store->is_trial ? 'Starter trial (ends '.($store->plan_expires_at?->toDateString() ?? '—').')' : ucfirst($store->plan)],
        ]);

        return self::SUCCESS;
    }

    /**
     * Read a value from the option, the interactive prompt, or the default.
     * Staying silent with --no-interaction keeps the command scriptable for
     * the first production account.
     */
    protected function optionOrAsk(string $option, string $question, string $default = ''): string
    {
        $value = $this->option($option);

        if ($value !== null && $value !== '') {
            return trim((string) $value);
        }

        if (! $this->input->isInteractive()) {
            return $default;
        }

        return trim((string) $this->ask($question, $default));
    }
}
