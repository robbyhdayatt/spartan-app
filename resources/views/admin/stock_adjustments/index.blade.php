@extends('adminlte::page')

@section('title', 'Adjusment Stok')

@section('content_header')
    <h1>Adjusment Stok</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan Adjusment</h3>
        <div class="card-tools">
            @can('can-manage-stock')
            <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Adjusment Baru</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
         @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
         @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table id="stock_adjustment-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Detail Part</th>
                    <th>Gudang / Rak</th>
                    <th class="text-center">Tipe</th>
                    <th class="text-center">Jumlah</th>
                    <th>Alasan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>
                        <strong>{{ $adj->part->nama_part }}</strong><br>
                        <small class="text-muted">{{ $adj->created_at->format('d M Y H:i') }}</small>
                    </td>
                    <td>{{ $adj->gudang->nama_gudang }} / {{ $adj->rak->nama_rak ?? 'N/A' }}</td>
                    <td class="text-center">
                        @if($adj->tipe == 'TAMBAH')
                            <span class="badge badge-success">TAMBAH</span>
                        @else
                            <span class="badge badge-danger">KURANG</span>
                        @endif
                    </td>
                    <td class="text-center font-weight-bold">{{ $adj->jumlah }}</td>
                    <td>
                        {{ $adj->alasan }}
                        @if($adj->status == 'REJECTED' && $adj->rejection_reason)
                           <br><small class="text-danger"><strong>Alasan Tolak:</strong> {{ $adj->rejection_reason }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($adj->status == 'PENDING_APPROVAL')
                            <span class="badge badge-warning">Menunggu</span>
                        @elseif($adj->status == 'APPROVED')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($adj->status == 'REJECTED')
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($adj->status === 'PENDING_APPROVAL')
                            @can('approve-adjustment', $adj)
                                <form action="{{ route('admin.stock-adjustments.approve', $adj->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger btn-xs" title="Reject" data-toggle="modal" data-target="#rejectModal-{{ $adj->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <small>Diproses oleh:<br>{{ $adj->approvedBy->nama ?? 'N/A' }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada permintaan adjusment.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Loop untuk membuat Modal untuk setiap item adjusment --}}
@foreach($adjustments as $adj)
@if($adj->status === 'PENDING_APPROVAL')
<div class="modal fade" id="rejectModal-{{ $adj->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel-{{ $adj->id }}" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.stock-adjustments.reject', $adj->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel-{{ $adj->id }}">Alasan Penolakan Adjusment</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="rejection_reason">Mohon berikan alasan penolakan:</label>
            <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Submit Penolakan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endforeach
@stop

@section('js')
<script>
    $(function () {
        $("#stock_adjustment-table").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "order": [[0, "desc"]],
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#stock_adjustment-table_wrapper .col-md-6:eq(0)');
    });
</script>
@stop
