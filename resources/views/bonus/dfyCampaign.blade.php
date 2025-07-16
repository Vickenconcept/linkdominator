@extends('layout.auth')

@section('content')
<h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
    DFY Profit Generating Campaigns
</h2>
<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
    <p>Hey there <i class="ti-hand-open"></i>,</p> 
    <p>Thanks for picking up this upsell.</p>
    <p>Indeed it is a guaranteed shortcut to consistent affiliate commissions.</p>
    <p>Here are the copy-and-paste campaigns;</p>
    <ul id="campaign-list"></ul>
    <p>Thanks.</p> 
</div>

<script>
    const links = [
        {
            label: 'Javascript Commission Bot',
            link: 'https://docs.google.com/document/d/1CG-d-ILUi44L5EtMp11kfHwiD8dTUdrBW_tIqnk2eIY/edit?usp=sharing'
        },
        {
            label: 'Cyclone',
            link: 'https://docs.google.com/document/d/1j3sVThlkGWx1AzxG-YyZxIGk2BJ-Qcj6D1xfAWn3nSU/edit?usp=sharing'
        },
        {
            label: 'ClipMagix',
            link: 'https://docs.google.com/document/d/19hsvH9xpG3JFCW6pMxwjmpne1ThXEoZFISBLfhOAh30/edit?usp=sharing'
        },
        {
            label: 'Stuff Your Bank',
            link: 'https://docs.google.com/document/d/16Z2Kyx85g-sDuzqGeJCQbUn0VjNuR821Iqun26kBe-Q/edit?usp=sharing'
        },
        {
            label: 'PWA by Mobifirst',
            link: 'https://docs.google.com/document/d/1a_Ufa8uzcHD79JyiAjC64ajr14v8WKzvJPI37wxU-V4/edit?usp=sharing'
        },
        {
            label: 'SWARM',
            link: 'https://docs.google.com/document/d/1jfHlIqkaUFL3S4tkuZFCq3eqazX4rJ6kWcG3QLg2trA/edit?usp=sharing'
        },
        {
            label: 'Preequell',
            link: 'https://docs.google.com/document/d/1fCnUDHkf_Zynw1VhAYINWb7V4ZiCvQfhFeRhAucVoqE/edit?usp=sharing'
        },
        {
            label: 'CloudFunnels',
            link: 'https://docs.google.com/document/d/1MfErvYZ4GoW5BMZbfYZZON-fB-nubxQSt3z0iK1teEc/edit?usp=sharing'
        },
        {
            label: 'Flux',
            link: 'https://docs.google.com/document/d/1DAf7WqnGw3ttkZ6S7e1tnbjSS0nPOF4tQIRiTRLratI/edit?usp=sharing'
        },
        {
            label: 'ShopZpresso',
            link: 'https://docs.google.com/document/d/1KMPVOK2aDrHF9fx55ZtaW5LqvlT6uBjEXOk1t7gSCr8/edit?usp=sharing'
        },
        {
            label: 'Profit Pixar',
            link: 'https://docs.google.com/document/d/1oUWcKdVlNzeHXlFAREFZzKcKI7nm8i5yJ-gyPqyNbRQ/edit?usp=sharing'
        },
        {
            label: 'RankSnap 3.0',
            link: 'https://docs.google.com/document/d/1Os0CJnQ6zb60EkBfuNmEe8oXKhddXdIn516ij9FODVg/edit?usp=sharing'
        },
        {
            label: 'Big Ticket Commissions',
            link: 'https://docs.google.com/document/d/1zcfQ4tH9-uf6Znswhl01g9gVtUg1EDjRofKwME6txKE/edit?usp=sharing'
        },
        {
            label: 'Profit-Tearz',
            link: 'https://docs.google.com/document/d/1N4G7T7m8YX6VdEZqQQZFilSionRyaGGE2HohL2OPi0Q/edit?usp=sharing'
        },
        {
            label: 'Ada Comply',
            link: 'https://docs.google.com/document/d/1e48D1e24YNvk_cXyjuYz8QWdXfSBOWW4vs9L02wxRzo/edit?usp=sharing'
        },
        {
            label: 'Surge',
            link: 'https://docs.google.com/document/d/1dVIMULdgRlZDlcU_4d30oV4ApSim99eBgkMoSnSybio/edit?usp=sharing'
        },
        {
            label: 'Swypio',
            link: 'https://docs.google.com/document/d/1LPZEVnk6uvMjOsFPpcFFiQmdA-5i8gujFEZnFpjRwKU/edit?usp=sharing'
        },
        {
            label: 'CommissionReplicator',
            link: 'https://docs.google.com/document/d/148zwYeOH0lIp1mpS6YSF1hQ6IT7yK12oCvbnjvUTDeE/edit?usp=sharing'
        },
        {
            label: 'Storypal',
            link: 'https://docs.google.com/document/d/19CUfIfST6JdjxWmUNqmhSjtTb6I6ivhAovOvu3vewf0/edit?usp=sharing'
        },
        {
            label: 'Flickstr',
            link: 'https://docs.google.com/document/d/1N7enGrp_g-ldaAZ2GFny4T5oFgnC6bRMJsTECtuHgtA/edit?usp=sharing'
        }
    ];

    let display = ''
    $.each(links, function(i, item) {
        display = `
            <li>
                <a href="${item.link}" target="_blank" class="text-blue-500">${item.label}</a>
            </li>    
        `;
        $('#campaign-list').append(display)
    })
</script>
@endsection