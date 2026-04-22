@extends('errors.layout')

@section('title', 'Accès refusé')
@section('code', 'Erreur 403')
@section('message', 'Cette page est protégée')
@section('description')
    Vous n'avez pas les droits nécessaires pour accéder à cette ressource.
    Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur ou vérifiez que vous êtes connecté avec le bon compte.
@endsection
