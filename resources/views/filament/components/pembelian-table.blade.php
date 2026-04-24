<table class="table-auto w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">No Faktur</th>
            <th class="border border-gray-300 px-4 py-2">Tanggal Pembelian</th>
            <th class="border border-gray-300 px-4 py-2">Total Tagihan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pembelians as $pembelian)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $pembelian->no_faktur }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $pembelian->tgl }}</td>
                <td class="border border-gray-300 px-4 py-2">
                    Rp{{ number_format($pembelian->total_tagihan, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="border border-gray-300 px-4 py-2 text-center text-gray-400">
                    Belum ada data. Klik tombol Proses terlebih dahulu.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>