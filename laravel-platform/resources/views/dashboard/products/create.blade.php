@extends('layouts.dashboard')

@section('title', 'إضافة منتج جديد')

@section('content')
@include('dashboard.products._form', ['product' => null, 'pageTitle' => 'إضافة منتج جديد', 'saveUrl' => '/api/v1/dashboard/products', 'method' => 'POST'])
@endsection
