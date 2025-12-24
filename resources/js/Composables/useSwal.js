import Swal from "sweetalert2";

export function useSwal() {
    // 1. Konfirmasi Hapus (Modal Tengah)
    const confirmDelete = (itemName, callback) => {
        Swal.fire({
            title: "Apakah Anda Yakin?",
            text: `Data "${itemName}" akan dihapus permanen!`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33", // Merah
            cancelButtonColor: "#3085d6", // Biru
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            customClass: {
                popup: "rounded-2xl shadow-xl", // Styling tambahan
            },
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    };

    // 2. Notifikasi Sukses/Gagal (Toast dari Samping Kanan)
    const toast = (icon, title) => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end", // Muncul di pojok kanan atas
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            },
        });

        Toast.fire({
            icon: icon,
            title: title,
        });
    };

    return {
        confirmDelete,
        toast,
    };
}
