@extends('layouts.dashboard')

@section('title', 'تعديل: ' . ($product->name_ar ?? $product->name))

@section('content')
@include('dashboard.products._form', [
    'product' => $product,
    'pageTitle' => 'تعديل المنتج',
    'saveUrl' => '/api/v1/dashboard/products/' . $product->id,
    'method' => 'PUT',
])
@endsection
