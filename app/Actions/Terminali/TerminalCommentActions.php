<?php

namespace App\Actions\Terminali;
use App\Models\TerminalLokacija;
use App\Models\TerminalComment;
use Illuminate\Support\Facades\DB;  

class TerminalCommentActions{

    public static function Create($terminalLokacijaId, $comment){
        TerminalLokacija::find($terminalLokacijaId)->comments()
            ->create([
                'comment' => $comment,
                'userId' => auth()->user()->id,
            ]);

        TerminalCommentActions::ComentNumberUpdate($terminalLokacijaId);
    }

    public static function Comments($terminalLokacijaId){
        return TerminalLokacija::find($terminalLokacijaId)->comments()->where('is_active', true)->get();
    }

    public static function Delete($terminalLokacijaCommentId){
         $komentar = TerminalComment::where('id', $terminalLokacijaCommentId)->first();
        
        if($komentar){
            $komentar->update(['is_active' => false, 'deleted_at' => now()]);
        }
        //TerminalComment::find($terminalLokacijaCommentId)->delete();

        TerminalCommentActions::ComentNumberUpdate($terminalLokacijaCommentId);
    }

    public static function ComentNumberUpdate($terminalLokacijaId){
        TerminalLokacija::where('id', $terminalLokacijaId)
            ->update([
                'br_komentara'          => TerminalLokacija::find($terminalLokacijaId)->comments()->where('is_active', true)->count(), 
                'last_comment_userId'   => auth()->user()->id, 
                'last_comment_at'       => now()
            ]);
    }
}


