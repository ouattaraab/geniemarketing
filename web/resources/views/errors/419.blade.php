@extends('errors.layout')

@section('title', 'Session expirée')
@section('code', 'Erreur 419')
@section('message', 'Votre session a expiré')
@section('description')
    Pour votre sécurité, nous avons interrompu votre session inactive.
    Rechargez la page et reconnectez-vous pour continuer.
@endsection
