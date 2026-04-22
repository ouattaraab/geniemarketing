@extends('errors.layout')

@section('title', 'Page introuvable')
@section('code', 'Erreur 404')
@section('message', 'Cette page n\'existe pas, ou plus')
@section('description')
    L'article ou la page que vous cherchez a peut-être été retiré, renommé, ou n'a jamais existé.
    Les analyses récentes vous attendent sur la <a href="{{ url('/') }}" class="text-gm-red underline">Une du magazine</a>.
@endsection
