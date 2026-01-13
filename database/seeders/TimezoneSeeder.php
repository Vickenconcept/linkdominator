<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing timezones
        DB::table('timezones')->truncate();

        $timezones = [
            [1, 'AF', 'Afghanistan', 'Asia/Kabul', 'UTC +04:30', 'https://timezonedb.com/time-zones/Asia/Kabul'],
            [2, 'AL', 'Albania', 'Europe/Tirane', 'UTC +02:00', 'https://timezonedb.com/time-zones/Europe/Tirane'],
            [3, 'DZ', 'Algeria', 'Africa/Algiers', 'UTC +01:00', 'https://timezonedb.com/time-zones/Africa/Algiers'],
            [4, 'AS', 'American Samoa', 'Pacific/Pago_Pago', 'UTC -11:00', 'https://timezonedb.com/time-zones/Pacific/Pago_Pago'],
            [5, 'AD', 'Andorra', 'Europe/Andorra', 'UTC +02:00', 'https://timezonedb.com/time-zones/Europe/Andorra'],
            [6, 'AO', 'Angola', 'Africa/Luanda', 'UTC +01:00', 'https://timezonedb.com/time-zones/Africa/Luanda'],
            [7, 'AI', 'Anguilla', 'America/Anguilla', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Anguilla'],
            [8, 'AQ', 'Antarctica', 'Antarctica/Casey', 'UTC +11:00', 'https://timezonedb.com/time-zones/Antarctica/Casey'],
            [9, 'AQ', 'Antarctica', 'Antarctica/Davis', 'UTC +07:00', 'https://timezonedb.com/time-zones/Antarctica/Davis'],
            [10, 'AQ', 'Antarctica', 'Antarctica/DumontDUrville', 'UTC +10:00', 'https://timezonedb.com/time-zones/Antarctica/DumontDUrville'],
            [11, 'AQ', 'Antarctica', 'Antarctica/Mawson', 'UTC +05:00', 'https://timezonedb.com/time-zones/Antarctica/Mawson'],
            [12, 'AQ', 'Antarctica', 'Antarctica/McMurdo', 'UTC +13:00', 'https://timezonedb.com/time-zones/Antarctica/McMurdo'],
            [13, 'AQ', 'Antarctica', 'Antarctica/Palmer', 'UTC -03:00', 'https://timezonedb.com/time-zones/Antarctica/Palmer'],
            [14, 'AQ', 'Antarctica', 'Antarctica/Rothera', 'UTC -03:00', 'https://timezonedb.com/time-zones/Antarctica/Rothera'],
            [15, 'AQ', 'Antarctica', 'Antarctica/Syowa', 'UTC +03:00', 'https://timezonedb.com/time-zones/Antarctica/Syowa'],
            [16, 'AQ', 'Antarctica', 'Antarctica/Troll', 'UTC +02:00', 'https://timezonedb.com/time-zones/Antarctica/Troll'],
            [17, 'AQ', 'Antarctica', 'Antarctica/Vostok', 'UTC +06:00', 'https://timezonedb.com/time-zones/Antarctica/Vostok'],
            [18, 'AG', 'Antigua and Barbuda', 'America/Antigua', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Antigua'],
            [19, 'AR', 'Argentina', 'America/Argentina/Buenos_Aires', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Buenos_Aires'],
            [20, 'AR', 'Argentina', 'America/Argentina/Catamarca', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Catamarca'],
            [21, 'AR', 'Argentina', 'America/Argentina/Cordoba', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Cordoba'],
            [22, 'AR', 'Argentina', 'America/Argentina/Jujuy', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Jujuy'],
            [23, 'AR', 'Argentina', 'America/Argentina/La_Rioja', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/La_Rioja'],
            [24, 'AR', 'Argentina', 'America/Argentina/Mendoza', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Mendoza'],
            [25, 'AR', 'Argentina', 'America/Argentina/Rio_Gallegos', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Rio_Gallegos'],
            [26, 'AR', 'Argentina', 'America/Argentina/Salta', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Salta'],
            [27, 'AR', 'Argentina', 'America/Argentina/San_Juan', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/San_Juan'],
            [28, 'AR', 'Argentina', 'America/Argentina/San_Luis', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/San_Luis'],
            [29, 'AR', 'Argentina', 'America/Argentina/Tucuman', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Tucuman'],
            [30, 'AR', 'Argentina', 'America/Argentina/Ushuaia', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Argentina/Ushuaia'],
            [31, 'AM', 'Armenia', 'Asia/Yerevan', 'UTC +04:00', 'https://timezonedb.com/time-zones/Asia/Yerevan'],
            [32, 'AW', 'Aruba', 'America/Aruba', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Aruba'],
            [33, 'AU', 'Australia', 'Antarctica/Macquarie', 'UTC +10:00', 'https://timezonedb.com/time-zones/Antarctica/Macquarie'],
            [34, 'AU', 'Australia', 'Australia/Adelaide', 'UTC +09:30', 'https://timezonedb.com/time-zones/Australia/Adelaide'],
            [35, 'AU', 'Australia', 'Australia/Brisbane', 'UTC +10:00', 'https://timezonedb.com/time-zones/Australia/Brisbane'],
            [36, 'AU', 'Australia', 'Australia/Broken_Hill', 'UTC +09:30', 'https://timezonedb.com/time-zones/Australia/Broken_Hill'],
            [37, 'AU', 'Australia', 'Australia/Darwin', 'UTC +09:30', 'https://timezonedb.com/time-zones/Australia/Darwin'],
            [38, 'AU', 'Australia', 'Australia/Eucla', 'UTC +08:45', 'https://timezonedb.com/time-zones/Australia/Eucla'],
            [39, 'AU', 'Australia', 'Australia/Hobart', 'UTC +10:00', 'https://timezonedb.com/time-zones/Australia/Hobart'],
            [40, 'AU', 'Australia', 'Australia/Lindeman', 'UTC +10:00', 'https://timezonedb.com/time-zones/Australia/Lindeman'],
            [41, 'AU', 'Australia', 'Australia/Lord_Howe', 'UTC +10:30', 'https://timezonedb.com/time-zones/Australia/Lord_Howe'],
            [42, 'AU', 'Australia', 'Australia/Melbourne', 'UTC +10:00', 'https://timezonedb.com/time-zones/Australia/Melbourne'],
            [43, 'AU', 'Australia', 'Australia/Perth', 'UTC +08:00', 'https://timezonedb.com/time-zones/Australia/Perth'],
            [44, 'AU', 'Australia', 'Australia/Sydney', 'UTC +10:00', 'https://timezonedb.com/time-zones/Australia/Sydney'],
            [45, 'AT', 'Austria', 'Europe/Vienna', 'UTC +02:00', 'https://timezonedb.com/time-zones/Europe/Vienna'],
            [46, 'AZ', 'Azerbaijan', 'Asia/Baku', 'UTC +04:00', 'https://timezonedb.com/time-zones/Asia/Baku'],
            [47, 'BS', 'Bahamas', 'America/Nassau', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Nassau'],
            [48, 'BH', 'Bahrain', 'Asia/Bahrain', 'UTC +03:00', 'https://timezonedb.com/time-zones/Asia/Bahrain'],
            [49, 'BD', 'Bangladesh', 'Asia/Dhaka', 'UTC +06:00', 'https://timezonedb.com/time-zones/Asia/Dhaka'],
            [50, 'BB', 'Barbados', 'America/Barbados', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Barbados'],
            [51, 'BY', 'Belarus', 'Europe/Minsk', 'UTC +03:00', 'https://timezonedb.com/time-zones/Europe/Minsk'],
            [52, 'BE', 'Belgium', 'Europe/Brussels', 'UTC +02:00', 'https://timezonedb.com/time-zones/Europe/Brussels'],
            [53, 'BZ', 'Belize', 'America/Belize', 'UTC -06:00', 'https://timezonedb.com/time-zones/America/Belize'],
            [54, 'BJ', 'Benin', 'Africa/Porto-Novo', 'UTC +01:00', 'https://timezonedb.com/time-zones/Africa/Porto-Novo'],
            [55, 'BM', 'Bermuda', 'Atlantic/Bermuda', 'UTC -03:00', 'https://timezonedb.com/time-zones/Atlantic/Bermuda'],
            [56, 'BT', 'Bhutan', 'Asia/Thimphu', 'UTC +06:00', 'https://timezonedb.com/time-zones/Asia/Thimphu'],
            [57, 'BO', 'Bolivia, Plurinational State of', 'America/La_Paz', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/La_Paz'],
            [58, 'BQ', 'Bonaire, Sint Eustatius and Saba', 'America/Kralendijk', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Kralendijk'],
            [59, 'BA', 'Bosnia and Herzegovina', 'Europe/Sarajevo', 'UTC +02:00', 'https://timezonedb.com/time-zones/Europe/Sarajevo'],
            [60, 'BW', 'Botswana', 'Africa/Gaborone', 'UTC +02:00', 'https://timezonedb.com/time-zones/Africa/Gaborone'],
            [61, 'BR', 'Brazil', 'America/Araguaina', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Araguaina'],
            [62, 'BR', 'Brazil', 'America/Bahia', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Bahia'],
            [63, 'BR', 'Brazil', 'America/Belem', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Belem'],
            [64, 'BR', 'Brazil', 'America/Boa_Vista', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Boa_Vista'],
            [65, 'BR', 'Brazil', 'America/Campo_Grande', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Campo_Grande'],
            [66, 'BR', 'Brazil', 'America/Cuiaba', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Cuiaba'],
            [67, 'BR', 'Brazil', 'America/Eirunepe', 'UTC -05:00', 'https://timezonedb.com/time-zones/America/Eirunepe'],
            [68, 'BR', 'Brazil', 'America/Fortaleza', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Fortaleza'],
            [69, 'BR', 'Brazil', 'America/Maceio', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Maceio'],
            [70, 'BR', 'Brazil', 'America/Manaus', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Manaus'],
            [71, 'BR', 'Brazil', 'America/Noronha', 'UTC -02:00', 'https://timezonedb.com/time-zones/America/Noronha'],
            [72, 'BR', 'Brazil', 'America/Porto_Velho', 'UTC -04:00', 'https://timezonedb.com/time-zones/America/Porto_Velho'],
            [73, 'BR', 'Brazil', 'America/Recife', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Recife'],
            [74, 'BR', 'Brazil', 'America/Rio_Branco', 'UTC -05:00', 'https://timezonedb.com/time-zones/America/Rio_Branco'],
            [75, 'BR', 'Brazil', 'America/Santarem', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Santarem'],
            [76, 'BR', 'Brazil', 'America/Sao_Paulo', 'UTC -03:00', 'https://timezonedb.com/time-zones/America/Sao_Paulo'],
            [77, 'IO', 'British Indian Ocean Territory', 'Indian/Chagos', 'UTC +06:00', 'https://timezonedb.com/time-zones/Indian/Chagos'],
            [78, 'BN', 'Brunei Darussalam', 'Asia/Brunei', 'UTC +08:00', 'https://timezonedb.com/time-zones/Asia/Brunei'],
            [79, 'BG', 'Bulgaria', 'Europe/Sofia', 'UTC +03:00', 'https://timezonedb.com/time-zones/Europe/Sofia'],
            [80, 'BF', 'Burkina Faso', 'Africa/Ouagadougou', 'UTC', 'https://timezonedb.com/time-zones/Africa/Ouagadougou'],
        ];

        // Insert timezones with explicit IDs
        foreach ($timezones as $timezone) {
            DB::table('timezones')->insert([
                'id' => $timezone[0],
                'country_code' => $timezone[1],
                'country_name' => $timezone[2],
                'time_zone' => $timezone[3],
                'gmt_offset' => $timezone[4],
                'timezone_link' => $timezone[5],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeded ' . count($timezones) . ' timezones.');
        $this->command->warn('Note: You have ~418 timezones in your database. Add the remaining timezones to this seeder or import them from your SQL dump.');
    }
}
