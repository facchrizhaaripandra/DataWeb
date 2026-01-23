@extends('layouts.app')

@section('title', 'Merge Datasets')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-object-group"></i> Merge Datasets
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Merge Operations
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-layer-group"></i> Union Merge
                                </h6>
                            </div>
                            <div class="card-body">
                                <p>Gabungkan baris dari dua dataset dengan kolom yang sama.</p>
                                <ul class="small">
                                    <li>Menghasilkan semua baris dari kedua dataset</li>
                                    <li>Kolom yang tidak ada akan diisi NULL</li>
                                    <li>Cocok untuk data dengan struktur serupa</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-link"></i> Join Merge
                                </h6>
                            </div>
                            <div class="card-body">
                                <p>Gabungkan kolom dari dua dataset berdasarkan posisi baris.</p>
                                <ul class="small">
                                    <li>Menggabungkan kolom secara vertikal</li>
                                    <li>Jumlah baris sesuai dataset terkecil</li>
                                    <li>Cocok untuk data terkait per baris</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-expand-alt"></i> Concatenate Merge
                                </h6>
                            </div>
                            <div class="card-body">
                                <p>Gabungkan kolom secara horizontal dengan prefix nama dataset.</p>
                                <ul class="small">
                                    <li>Menambahkan prefix pada nama kolom</li>
                                    <li>Jumlah baris sesuai dataset terkecil</li>
                                    <li>Cocok untuk data yang tidak terkait langsung</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('merge.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle"></i> Start New Merge
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection