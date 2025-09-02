@extends('adminlte::page')
@section('title', 'Manajemen Campaign')
@section('content_header')
    <h1>Manajemen Campaign</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Campaign Promosi</h3>
        <div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">Buat Campaign Baru</button></div>
    </div>
    <div class="card-body">
        {{-- Session messages and errors --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Campaign</th>
                    <th>Tipe</th>
                    <th>Part</th>
                    <th>Harga Promo</th>
                    <th>Periode Aktif</th>
                    <th>Status</th>
                    <th style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->nama_campaign }}</td>
                    <td>{{ $campaign->tipe }}</td>
                    <td>{{ $campaign->part->nama_part ?? 'N/A' }}</td>
                    <td>Rp {{ number_format($campaign->harga_promo, 0, ',', '.') }}</td>
                    <td>{{ $campaign->tanggal_mulai->format('d M Y') }} - {{ $campaign->tanggal_selesai->format('d M Y') }}</td>
                    <td>@if($campaign->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-danger">Non-Aktif</span>@endif</td>
                    <td>
                        <button class="btn btn-warning btn-xs edit-btn" data-id="{{ $campaign->id }}" data-campaign='@json($campaign)' data-toggle="modal" data-target="#editModal">Edit</button>
                        <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">Belum ada campaign yang dibuat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $campaigns->links() }}</div>
</div>

@include('admin.campaigns.modals', ['parts' => $parts])

@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    function showDefaultPrice(selectElement, priceType) {
        var selectedOption = $(selectElement).find('option:selected');
        var defaultPrice = priceType === 'PEMBELIAN' ? selectedOption.data('default-buy-price') : selectedOption.data('default-sell-price');
        var priceInfo = $(selectElement).closest('.form-group').next().find('.default-price-info');
        var priceLabel = priceType === 'PEMBELIAN' ? 'Harga beli default: Rp ' : 'Harga jual default: Rp ';

        if (defaultPrice !== undefined) {
            priceInfo.text(priceLabel + new Intl.NumberFormat('id-ID').format(defaultPrice));
        } else { priceInfo.text(''); }
    }

    // Initialize Select2
    $('#createModal .part-select, #editModal .part-select').select2({ dropdownParent: $(this).closest('.modal') });
    $('#createModal .part-select').select2({ dropdownParent: $('#createModal') });
    $('#editModal .part-select').select2({ dropdownParent: $('#editModal') });

    // Event listener for campaign type change
    $('.modal').on('change', '.campaign-type-select', function() {
        var priceType = $(this).val();
        var partSelect = $(this).closest('.modal-body').find('.part-select');
        showDefaultPrice(partSelect, priceType);
    });

    $('.modal').on('change', '.part-select', function() {
        var priceType = $(this).closest('.modal-body').find('.campaign-type-select').val();
        showDefaultPrice(this, priceType);
    });

    // Event listener for edit button
    $('.edit-btn').on('click', function() {
        var campaign = $(this).data('campaign');
        $('#editForm').attr('action', "{{ url('admin/campaigns') }}/" + campaign.id);
        $('#edit_nama_campaign').val(campaign.nama_campaign);
        $('#edit_tipe').val(campaign.tipe);
        $('#edit_part_id').val(campaign.part_id).trigger('change');
        $('#edit_harga_promo').val(campaign.harga_promo);
        $('#edit_tanggal_mulai').val(campaign.tanggal_mulai);
        $('#edit_tanggal_selesai').val(campaign.tanggal_selesai);
        $('#edit_is_active').val(campaign.is_active);
        showDefaultPrice($('#edit_part_id'), campaign.tipe);
    });
});
</script>
@stop
