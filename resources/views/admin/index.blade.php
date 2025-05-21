@extends('layouts.app')

@section('title', 'Laporan Penggajian | Panel Admin')

@section('content')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Admin</h5>
                        <a href="{{ route('admin.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Admin
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($admin as $index => $a)
                                        <tr>
                                            <td>{{ $index + $admin->firstItem() }}</td>
                                            <td>{{ $a->nama }}</td>
                                            <td>{{ $a->email }}</td>
                                            <td>{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.show', $a->id) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.edit', $a->id) }}" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(auth()->id() != $a->id)
                                                        <form action="{{ route('admin.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada data admin</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $admin->links() }}
                        </div>
                    </div>
                </div>
                @endsection
