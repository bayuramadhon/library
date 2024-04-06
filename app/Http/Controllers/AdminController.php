<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Publisher;
use App\Models\Author;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Catalog;
use App\Models\Book;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Admincontroller extends Controller
{



    public function dashboard()
    {
        $total_anggota = Member::count();
        $total_buku = Book::count();
        $total_peminjaman = Transaction::whereMonth('date_start', date('m'))->count();
        $total_penerbit = Publisher::count();

        $data_donut = Book::select(DB::raw("COUNT(publisher_id)as total"))->groupBy('publisher_id')->orderBy('publisher_id', 'asc')->pluck('total');
        $label_donut = Publisher::orderBy('publishers.id', 'asc')->join('books', 'books.publisher_id', '=', 'publishers.id')->groupBy('name')->pluck('name');

        // $areachart = Book::select(DB::raw("COUNT(catalog_id)as total"))->groupBy('catalog_id')->orderBy('catalog_id', 'asc')->pluck('total');

        $area_chart = Book::select(DB::raw("COUNT(*) as count"), DB::raw("books.price as harga_buku"))
            ->where('price', range(1000, 17000))
            // // ->groupBy(DB::raw("harga_buku(price)"))
            ->pluck('count', 'harga_buku');

        $books_area_chart = Book::select('price')->whereBetween('price', [1000, 17000])->pluck('price');

        // dd($area_chart);

        // $area_chart= Book::select("price from book ")
        //             ->groupBy(DB::raw("book(price)"))
        //             ->pluck('count','price');

        $labels = $books_area_chart->keys();
        $data = $books_area_chart->values()->toArray();

        // return view('chart', compact('labels', 'data'));

        $label_bar = ['peminjaman', 'pengembalian'];
        $data_bar = [];

        foreach ($label_bar as $key => $value) {
            $data_bar[$key]['label'] = $label_bar[$key];
            $data_bar[$key]['backgroundColor'] = $key == 0 ? 'rgba(60,141,188,0.9)' : 'rgba(210,214,222,1';
            $data_month = [];

            foreach (range(1, 12) as $month) {
                if ($key == 0) {
                    $data_month[] = Transaction::select(DB::raw("COUNT(*)as total"))->whereMonth('date_start', $month)->first()->total;
                } else {
                    $data_month[] = Transaction::select(DB::raw("COUNT(*)as total"))->whereMonth('date_end', $month)->first()->total;
                }
            }

            $data_bar[$key]['data'] = $data_month;
        }


        return view('admin.dashboard', compact(
            'total_buku',
            'total_anggota',
            'total_peminjaman',
            'total_penerbit',
            'data_donut',
            'label_donut',
            'data_bar',
            'labels',
            'data'
        ));
    }

    public function catalog()
    {

        if (auth()->user()->can('index peminjaman')) {
            $data_katalog = catalog::all();

            return view('admin.catalog.catalog', compact('data_katalog'));
        }
    }

    public function publisher()
    {
        return view('admin.publisher');
    }

    public function author()
    {
        $data_author = author::all();

        return view('admin.author.author', compact('data_author'));
    }

    public function book()
    {
        $data_buku = book::all();

        return view('admin.book.book', compact('data_buku'));
    }

    public function test_spatie()
    {
        // $role = Role::create(['name' => 'petugas']);
        // $permission = Permission::create(['name' => 'index peminjaman']);

        // $role->givePermissionTo($permission);
        // $permission->assignRole($role);


        // $user = auth()->user();
        // $user->assignRole('petugas');
        // return $user;

        // $user = User::with('roles')->get();
        // return $user;

        // $user = User::where('id', 2)->first();
        // $user = auth()->user();
        // $user->removeRole('petugas');
    }
}
