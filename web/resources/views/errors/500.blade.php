@extends('errors.layout')

@section('title', 'Erreur serveur')
@section('code', 'Erreur 500')
@section('message', 'Un incident technique nous oblige à une interruption')
@section('description')
    Notre équipe a été notifiée et travaille à rétablir le service au plus vite.
    Merci pour votre patience — réessayez dans quelques instants.
@endsection
