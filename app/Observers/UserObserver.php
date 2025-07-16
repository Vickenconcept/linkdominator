<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AutoMessageResponse;
use Carbon\Carbon;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->access_token = bin2hex(random_bytes(32));
        $user->save();

        AutoMessageResponse::insert($this->autoMessageResponseSeeder($user->id));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        AutoMessageResponse::where('user_id', $user->id)->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    public function autoMessageResponseSeeder($userid)
    {
        return [
            [
                'message_type' => 'normal',
                'message_keywords' => 'birthday, ulang tahun, hari jadi, nejlepší, med dagen, Geburtstag, Muchas felicidades, 生日, anniversaire, 생일, compleanno, 生日, Gefeliciteerd, 誕生日, Gratulerer med dagen, Wszystkiego najlepszego, aniversário, La mulţi ani, födelsedagen, Kaarawan',
                'total_endorse_skills' => 0,
                'message_body' =>'Thank you very much @firstName for superb birthday wishes. It really mean a lot to me. 😍 work',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'message_type' => 'normal',
                'message_keywords' => 'endorsing, endorsment, merekomendasikan, menyokong, potvrdil, anerkendte, 能力, recommandé, 추천, confermato, 技能, onderschreven, 推薦, bestätigt, anerkjente, potwierdzenie, recomendar, recomandat, подтвердили, kompetens, endorso, valida',
                'total_endorse_skills' => 0,
                'message_body' =>'You\'re welcome @firstName! if you have a sec, could you please my endorse skills 😃 😍',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'message_type' => 'normal',
                'message_keywords' => 'new job, pekerjaan baru, pekerjaan baharu, novému zaměstnání, nye job, neuen Stelle, nuevo puesto, 新工作, nouvel emploi, 승진을, nuovo lavoro, 换了工作, nieuwe baan, 新ポジション, nye jobben, nowej pracy, novo emprego, noul job, новой работой, nya jobbet, bagong trabaho, งานใหม่, Yeni işiniz, الوظيفة الجديدة',
                'total_endorse_skills' => 0,
                'message_body' =>'Thank you very much @firstName for recognizing my efforts. I appreciate it!',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'message_type' => 'normal',
                'message_keywords' => 'work anniversary, anniversary kerja, ulang tahun kerjaya, vašemu výročí, Tillykke med jubilæet, Firmenjubiläum, aniversario de trabajo, 祝到職紀念日快樂, anniversaire professionnel, 입사기념일을 축하드려요, anniversario lavorativo, 纪念日快乐, jubileum op het werk, 勤続記念日, årsdagen på jobben, rocznicy pracy, aniversário de empresa',
                'total_endorse_skills' => 0,
                'message_body' =>'@firstName kindly accept my deepest appreciation to thank you for being so thoughtful and sweet by remembering my anniversary. Thanks a lot! hy linus',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'message_type' => 'endorsement',
                'message_keywords' => 'endorse-short-code',
                'total_endorse_skills' => 20,
                'message_body' =>'Hello @firstName, I\'ve endorsed all your skills... 😍',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'message_type' => 'followup',
                'message_keywords' => null,
                'total_endorse_skills' => 0,
                'message_body' =>'Thank you very much @firstName for connecting with me...😍',
                'attachement' => json_encode([
                    'status' => 'false',
                    'image' => 'false',
                    'file' => 'false'
                ]),
                'user_id' => $userid,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];
    }
}
