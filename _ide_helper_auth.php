<?php

/**
 * Samo za IDE (Intelephense) - ovaj fajl se nikad ne ucitava (nije u autoload-u).
 *
 * Globalni helper auth() vraca Contracts\Auth\Factory|Guard, pa auth()->user()
 * u IDE-u nije App\Models\User. barryvdh/laravel-ide-helper pokriva samo
 * Auth::user() fasadu, ovaj stub pokriva auth()->user().
 */

namespace Illuminate\Contracts\Auth {
    exit('Ovaj fajl sluzi samo IDE-u.');

    interface Factory
    {
        /**
         * @return \App\Models\User|null
         */
        public function user();
    }

    interface Guard
    {
        /**
         * @return \App\Models\User|null
         */
        public function user();
    }

    interface StatefulGuard extends Guard
    {
        /**
         * @return \App\Models\User|null
         */
        public function user();
    }
}
