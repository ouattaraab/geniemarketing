@extends('errors.layout')

@section('title', 'Trop de requêtes')
@section('code', 'Erreur 429')
@section('message', 'Trop de tentatives trop rapides')
@section('description')
    Nous limitons certaines actions pour protéger la plateforme.
    Patientez un instant avant de réessayer.
@endsection
