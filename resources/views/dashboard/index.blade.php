<x-app-layout>


    <!-- Leaflet JS and CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map { height: 400px; border-radius: 1rem; z-index: 1; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 shadow-xl rounded-xl sm:rounded-lg relative border border-blue-800 z-20">
                <div class="absolute inset-0 overflow-hidden rounded-lg pointer-events-none">
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white opacity-5 rounded-full transform scale-150 pointer-events-none"></div>
                </div>
                
                <div class="p-8 text-white relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-3xl font-extrabold tracking-tight mb-2 text-white drop-shadow-md">Pusat Komando 4DX</h3>
                        <p class="text-blue-200 font-medium text-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            PLN UID Jawa Barat - Real-time Resume
                        </p>
                    </div>
                    
                </div>
            </div>



            <div class="grid grid-cols-1 gap-6">
                <!-- Scoreboard WIGs -->
                <div class="w-full space-y-4">
                    <div class="bg-white rounded-xl sm:rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Progress Bar Pencapaian WIG dan LM
                            </h3>
                            
                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                                <div class="flex bg-gray-100 p-1 rounded-md">
                                    <button type="submit" name="periode_wig" value="bulanan" class="{{ ($periodeWig ?? 'bulanan') === 'bulanan' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded transition-all">Bulanan</button>
                                    <button type="submit" name="periode_wig" value="tahunan" class="{{ ($periodeWig ?? 'bulanan') === 'tahunan' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded transition-all">Tahunan</button>
                                </div>
                                
                                <div class="flex gap-2">
                                    <select name="bulan" onchange="this.form.submit()" class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $idx => $namaBulan)
                                            <option value="{{ $idx + 1 }}" {{ $bulan == ($idx + 1) ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <select name="tahun" onchange="this.form.submit()" class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                        @for($y = 2024; $y <= 2030; $y++)
                                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
                        
                        <div class="space-y-4">
                            @forelse($wigProgresses ?? [] as $wig)
                                <div x-data="{ expanded: false }" class="bg-gray-50/50 rounded-lg p-4 border border-gray-100 transition-all">
                                    <div @click="expanded = !expanded" class="cursor-pointer group flex justify-between items-center mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $wig['judul'] }}</h4>
                                                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                            <span class="text-[11px] text-gray-500">
                                                {{ $wig['deskripsi'] ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="text-right ml-4">
                                            @php
                                                $progressColor = $wig['progress'] >= 100 ? 'text-green-600' : ($wig['progress'] >= 95 ? 'text-yellow-500' : 'text-red-500');
                                                $bgColor = $wig['progress'] >= 100 ? 'bg-green-500' : ($wig['progress'] >= 95 ? 'bg-yellow-400' : 'bg-red-500');
                                            @endphp
                                            <span class="text-xl font-black {{ $progressColor }}">{{ $wig['progress'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                                        <div class="{{ $bgColor }} h-2 rounded-full transition-all duration-700 ease-out" style="width: {{ min($wig['progress'], 100) }}%"></div>
                                    </div>

                                    <!-- LM Drill-down List -->
                                    <div x-show="expanded" x-collapse x-cloak class="mt-4 pt-3 border-t border-gray-200/60 space-y-3">
                                        @if(isset($wig['lms']) && count($wig['lms']) > 0)
                                            @foreach($wig['lms'] as $lm)
                                                <div class="pl-4 border-l-2 border-blue-200">
                                                    <div class="flex justify-between items-end mb-1">
                                                        <div class="flex-1 pr-4">
                                                            <span class="text-xs font-semibold text-gray-700">{{ $lm['judul'] }}</span>
                                                            <div class="text-[10px] text-gray-500 mt-0.5">
                                                                Target: {{ number_format($lm['target'], 2) }} | Realisasi: {{ number_format($lm['realisasi'], 2) }} {{ $lm['satuan'] }} ({{ ucfirst($lm['polaritas']) }})
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            @php
                                                                $lmColor = $lm['progress'] >= 100 ? 'text-green-600' : ($lm['progress'] >= 95 ? 'text-yellow-500' : 'text-red-500');
                                                                $lmBg = $lm['progress'] >= 100 ? 'bg-green-500' : ($lm['progress'] >= 95 ? 'bg-yellow-400' : 'bg-red-500');
                                                            @endphp
                                                            <span class="text-sm font-bold {{ $lmColor }}">{{ $lm['progress'] }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="{{ $lmBg }} h-1.5 rounded-full transition-all duration-700" style="width: {{ min($lm['progress'], 100) }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-xs text-gray-400 italic py-1">Tidak ada data LM yang aktif.</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-gray-100">Belum ada WIG yang didefinisikan.</div>
                            @endforelse
                        </div>
                    </div>


                </div>


            </div>

            <!-- Rincian Performa WIG Section -->
<div class="grid grid-cols-1 gap-6 mb-6">
<div class="w-full space-y-4">
ÿþ                                                < s v g   c l a s s = " w - 5   h - 5   m r - 2   t e x t - g r a y - 4 0 0 "   f i l l = " n o n e "   s t r o k e = " c u r r e n t C o l o r "   v i e w B o x = " 0   0   2 4   2 4 " > < p a t h   s t r o k e - l i n e c a p = " r o u n d "   s t r o k e - l i n e j o i n = " r o u n d "   s t r o k e - w i d t h = " 2 "   d = " M 1 6   8 v 8 m - 4 - 5 v 5 m - 4 - 2 v 2 m - 2   4 h 1 2 a 2   2   0   0 0 2 - 2 V 6 a 2   2   0   0 0 - 2 - 2 H 6 a 2   2   0   0 0 - 2   2 v 1 2 a 2   2   0   0 0 2   2 z " > < / p a t h > < / s v g >  
                                                 S c o r e b o a r d   W I G   { {   $ i s U l p L e v e l   ?   ' U L P '   :   ( $ i s U p 3 L e v e l   ?   ' U P 3 '   :   ' U I D ' )   } }   ( M a t r i x )  
                                         < / h 3 >  
                                         < d i v   c l a s s = " s p a c e - y - 4 "   x - d a t a = " {   a c t i v e T a b :   { {   $ w i g s - > f i r s t ( ) - > i d   ? ?   ' n u l l '   } }   } " >  
                                                 < ! - -   T A B S   - - >  
                                                 @ i f ( $ w i g s - > c o u n t ( )   >   0 )  
                                                         < d i v   c l a s s = " f l e x   o v e r f l o w - x - a u t o   w h i t e s p a c e - n o w r a p   g a p - 2   m b - 2   b o r d e r - b   b o r d e r - g r a y - 2 0 0   p b - 3   c u s t o m - s c r o l l b a r "   s t y l e = " - w e b k i t - o v e r f l o w - s c r o l l i n g :   t o u c h ; " >  
                                                                 @ f o r e a c h ( $ w i g s   a s   $ w i g )  
                                                                         < b u t t o n   @ c l i c k = " a c t i v e T a b   =   { {   $ w i g - > i d   } } "  
                                                                                         : s t y l e = " a c t i v e T a b   = = =   { {   $ w i g - > i d   } }   ?   ' b a c k g r o u n d - c o l o r :   # 0 b 2 2 5 6 ;   c o l o r :   w h i t e ;   b o r d e r - c o l o r :   # 0 b 2 2 5 6 ;   b o x - s h a d o w :   0   4 p x   6 p x   - 1 p x   r g b a ( 0 ,   0 ,   0 ,   0 . 1 ) ,   0   2 p x   4 p x   - 1 p x   r g b a ( 0 ,   0 ,   0 ,   0 . 0 6 ) ; '   :   ' b a c k g r o u n d - c o l o r :   # f 3 f 4 f 6 ;   c o l o r :   # 4 b 5 5 6 3 ;   b o r d e r - c o l o r :   # d 1 d 5 d b ; ' "  
                                                                                         c l a s s = " p x - 4   p y - 2   r o u n d e d - m d   t e x t - x s   f o n t - b o l d   t r a n s i t i o n - a l l   b o r d e r   o u t l i n e - n o n e   h o v e r : b g - g r a y - 2 0 0   f l e x - s h r i n k - 0 " >  
                                                                                 { {   $ w i g - > j u d u l   } }  
                                                                         < / b u t t o n >  
                                                                 @ e n d f o r e a c h  
                                                         < / d i v >  
                                                 @ e n d i f  
  
                                                 @ f o r e l s e ( $ w i g s   a s   $ w i g )  
                                                         @ p h p  
                                                                 $ p c t U i d   =   $ w i g - > c a p a i a n ;  
                                                                 $ i s E x c e e d   =   $ p c t U i d   > =   1 0 0 ;  
                                                                 $ w i g L m s   =   $ l m s - > w h e r e ( ' w i g _ i d ' ,   $ w i g - > i d ) ;  
                                                                 $ b u l a n T   =   \ C a r b o n \ C a r b o n : : p a r s e ( $ s e s i _ w i g - > t a n g g a l _ p e l a k s a n a a n ) - > m o n t h ;  
                                                                  
                                                                 $ n a m a B u l a n T a r g e t   =   [ ' J a n u a r i ' ,   ' F e b r u a r i ' ,   ' M a r e t ' ,   ' A p r i l ' ,   ' M e i ' ,   ' J u n i ' ,   ' J u l i ' ,   ' A g u s t u s ' ,   ' S e p t e m b e r ' ,   ' O k t o b e r ' ,   ' N o v e m b e r ' ,   ' D e s e m b e r ' ] [ $ t a r g e t B u l a n   -   1 ]   ? ?   ' ' ;  
                                                                 $ n a m a B u l a n P r e v   =   [ ' J a n u a r i ' ,   ' F e b r u a r i ' ,   ' M a r e t ' ,   ' A p r i l ' ,   ' M e i ' ,   ' J u n i ' ,   ' J u l i ' ,   ' A g u s t u s ' ,   ' S e p t e m b e r ' ,   ' O k t o b e r ' ,   ' N o v e m b e r ' ,   ' D e s e m b e r ' ] [ $ p r e v B u l a n   -   1 ]   ? ?   ' ' ;  
                                                         @ e n d p h p  
                                                          
                                                         < d i v   x - s h o w = " a c t i v e T a b   = = =   { {   $ w i g - > i d   } } "   x - c l o a k   c l a s s = " b g - w h i t e   p - 4   r o u n d e d - x l   b o r d e r   b o r d e r - g r a y - 2 0 0   s h a d o w - s m   t r a n s i t i o n - a l l   d u r a t i o n - 3 0 0 " >  
                                                                 < ! - -   I n f o g r a f i s   W I G   &   L M   - - >  
                                                                 < d i v   c l a s s = " f l e x   f l e x - c o l   x l : f l e x - r o w   i t e m s - s t a r t   g a p - 5   m b - 6 " >  
                                                                         < ! - -   K I R I :   W I G   C a r d   &   T a b e l   W I G   - - >  
                                                                         < d i v   c l a s s = " w - f u l l   x l : w - [ 5 5 % ]   f l e x   f l e x - c o l   g a p - 4 " >  
                                                                                 < ! - -   W I G   C a r d   - - >  
                                                                                 < d i v   c l a s s = " w - f u l l   b g - w h i t e   r o u n d e d - l g   s h a d o w - s m   b o r d e r   b o r d e r - [ # 0 b 2 2 5 6 ]   o v e r f l o w - h i d d e n   f l e x   f l e x - c o l " >  
                                                                                         < d i v   c l a s s = " b g - [ # 0 b 2 2 5 6 ]   t e x t - w h i t e   p x - 3   p y - 2   t e x t - [ 1 0 p x ]   f o n t - b o l d   u p p e r c a s e   t r u n c a t e " >  
                                                                                                 W I G   P E R F O R M A N C E   |   { {   $ i s E x c e e d   ?   ' E X C E E D E D   T A R G E T '   :   ' P E R F O R M A N C E   W A T C H '   } }  
                                                                                         < / d i v >  
                                                                                         < d i v   c l a s s = " p - 3   f l e x - 1   f l e x   f l e x - c o l   s m : f l e x - r o w " >  
                                                                                                 < d i v   c l a s s = " w - f u l l   s m : w - 1 / 2   t e x t - c e n t e r   f l e x   f l e x - c o l   j u s t i f y - c e n t e r   i t e m s - c e n t e r   p x - 2   p y - 2 " >  
                                                                                                         < d i v   c l a s s = " t e x t - 2 x l   f o n t - b o l d   t e x t - [ # 0 b 2 2 5 6 ] " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ t a r g e t   ? ?   0 ,   2 )   } }   < s p a n   c l a s s = " t e x t - s m " > { {   $ w i g - > s a t u a n - > n a m e   ? ?   ' '   } } < / s p a n > < / d i v >  
                                                                                                         < d i v   c l a s s = " t e x t - [ 1 0 p x ]   f o n t - b o l d   t e x t - g r a y - 7 0 0   m t - 1 " > T a r g e t   W I G   { {   $ i s U l p L e v e l   ?   ' U L P '   :   ( $ i s U p 3 L e v e l   ?   ' U P 3 '   :   ' U I D   J a b a r ' )   } } < / d i v >  
                                                                                                         < d i v   c l a s s = " t e x t - [ 9 p x ]   t e x t - g r a y - 5 0 0   m t - 1 " > R e a l i s a s i :   { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ r e a l i s a s i   ? ?   0 ,   2 )   } }   { {   $ w i g - > s a t u a n - > n a m e   ? ?   ' '   } } < / d i v >  
                                                                                                 < / d i v >  
                                                                                                 < d i v   c l a s s = " w - f u l l   s m : w - 1 / 2   b o r d e r - t   s m : b o r d e r - t - 0   s m : b o r d e r - l   b o r d e r - g r a y - 2 0 0   m t - 2   s m : m t - 0   p t - 2   s m : p t - 0   s m : p l - 3   f l e x   f l e x - c o l   j u s t i f y - b e t w e e n " >  
                                                                                                         < d i v >  
                                                                                                                 < d i v   c l a s s = " t e x t - [ 9 p x ]   f o n t - b o l d   t e x t - g r a y - 8 0 0   u p p e r c a s e   m b - 1   p b - 1   b o r d e r - b   b o r d e r - g r a y - 1 0 0 " >  
                                                                                                                         C A P A I A N   W I G   B U L A N   L A L U   ( { {   s t r t o u p p e r ( $ n a m a B u l a n P r e v )   } } )  
                                                                                                                 < / d i v >  
                                                                                                                 < d i v   c l a s s = " f l e x   i t e m s - c e n t e r   g a p - 3 " >  
                                                                                                                         < d i v   c l a s s = " t e x t - x l   f o n t - b l a c k   { {   ( $ w i g - > c a p a i a n _ p r e v   ? ?   0 )   > =   1 0 0   ?   ' t e x t - g r e e n - 6 0 0 '   :   ' t e x t - o r a n g e - 5 0 0 '   } } " >  
                                                                                                                                 { {   n u m b e r _ f o r m a t ( $ w i g - > c a p a i a n _ p r e v   ? ?   0 ,   2 )   } }   %  
                                                                                                                         < / d i v >  
                                                                                                                         < d i v   c l a s s = " f l e x   f l e x - c o l " >  
                                                                                                                                 < d i v   c l a s s = " t e x t - [ 9 p x ]   t e x t - g r a y - 5 0 0   f o n t - m e d i u m " > T a r g e t :   < s p a n   c l a s s = " f o n t - b o l d   t e x t - g r a y - 7 0 0 " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ t a r g e t _ p r e v   ? ?   0 ,   2 )   } } < / s p a n > < / d i v >  
                                                                                                                                 < d i v   c l a s s = " t e x t - [ 9 p x ]   t e x t - g r a y - 5 0 0   f o n t - m e d i u m " > R e a l i s a s i :   < s p a n   c l a s s = " f o n t - b o l d   t e x t - g r a y - 7 0 0 " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ r e a l i s a s i _ p r e v   ? ?   0 ,   2 )   } } < / s p a n > < / d i v >  
                                                                                                                         < / d i v >  
                                                                                                                 < / d i v >  
                                                                                                         < / d i v >  
                                                                                                         < d i v   c l a s s = " m t - 2 " >  
                                                                                                                 < d i v   c l a s s = " t e x t - [ 8 p x ]   f o n t - b o l d   t e x t - g r a y - 4 0 0   t e x t - c e n t e r   m b - 0 . 5 " > T R E N D   W I G   ( % ) < / d i v >  
                                                                                                                 < d i v   c l a s s = " h - 6   r e l a t i v e   w - f u l l   g r o u p " >  
                                                                                                                         @ p h p  
                                                                                                                                 $ t r e n d V a l u e s   =   $ w i g - > t r e n d _ c a p a i a n   ? ?   [ ] ;  
                                                                                                                                 $ m a x V a l   =   m a x ( 1 0 0 ,   c o u n t ( $ t r e n d V a l u e s )   ?   m a x ( $ t r e n d V a l u e s )   :   0 )   *   1 . 1 ;  
                                                                                                                                 i f   ( $ m a x V a l   = =   0 )   $ m a x V a l   =   1 0 0 ;  
                                                                                                                                  
                                                                                                                                 $ x S t e p   =   $ b u l a n T   >   1   ?   9 0   /   ( $ b u l a n T   -   1 )   :   0 ;  
                                                                                                                                 $ p o i n t s   =   [ ] ;  
                                                                                                                                 $ x P o s   =   5 ;  
                                                                                                                                  
                                                                                                                                 f o r ( $ i = 1 ;   $ i < = $ b u l a n T ;   $ i + + )   {  
                                                                                                                                         $ v a l   =   $ t r e n d V a l u e s [ $ i ]   ? ?   0 ;  
                                                                                                                                         $ y P o s   =   9 0   -   ( $ v a l   /   $ m a x V a l )   *   8 0 ;  
                                                                                                                                         $ p o i n t s [ ]   =   " { $ x P o s } , { $ y P o s } " ;  
                                                                                                                                         $ x P o s   + =   $ x S t e p ;  
                                                                                                                                 }  
                                                                                                                                 $ p o i n t s S t r   =   i m p l o d e ( "   " ,   $ p o i n t s ) ;  
                                                                                                                         @ e n d p h p  
                                                                                                                          
                                                                                                                         < s v g   v i e w B o x = " 0   0   1 0 0   1 0 0 "   c l a s s = " a b s o l u t e   i n s e t - 0   w - f u l l   h - f u l l "   p r e s e r v e A s p e c t R a t i o = " n o n e " >  
                                                                                                                                 < p o l y l i n e   p o i n t s = " { {   $ p o i n t s S t r   } } "   f i l l = " n o n e "   s t r o k e = " # 3 b 8 2 f 6 "   s t r o k e - w i d t h = " 1 . 5 "   v e c t o r - e f f e c t = " n o n - s c a l i n g - s t r o k e "   s t r o k e - l i n e c a p = " r o u n d "   s t r o k e - l i n e j o i n = " r o u n d "   / >  
                                                                                                                         < / s v g >  
                                                                                                                          
                                                                                                                         @ p h p   $ x P o s   =   5 ;   @ e n d p h p  
                                                                                                                         @ f o r ( $ i = 1 ;   $ i < = $ b u l a n T ;   $ i + + )  
                                                                                                                                 @ p h p  
                                                                                                                                         $ v a l   =   $ t r e n d V a l u e s [ $ i ]   ? ?   0 ;  
                                                                                                                                         $ y P o s   =   9 0   -   ( $ v a l   /   $ m a x V a l )   *   8 0 ;  
                                                                                                                                 @ e n d p h p  
                                                                                                                                 < d i v   c l a s s = " a b s o l u t e   w - [ 7 p x ]   h - [ 7 p x ]   b g - w h i t e   b o r d e r   b o r d e r - b l u e - 5 0 0   r o u n d e d - f u l l   h o v e r : b g - b l u e - 1 0 0   h o v e r : s c a l e - 1 2 5   t r a n s i t i o n - t r a n s f o r m "    
                                                                                                                                           s t y l e = " l e f t :   { {   $ x P o s   } } % ;   t o p :   { {   $ y P o s   } } % ;   t r a n s f o r m :   t r a n s l a t e ( - 5 0 % ,   - 5 0 % ) ;   c u r s o r :   p o i n t e r ; "  
                                                                                                                                           t i t l e = " B u l a n   { {   [ ' J A N ' , ' F E B ' , ' M A R ' , ' A P R ' , ' M E I ' , ' J U N ' , ' J U L ' , ' A G U ' , ' S E P ' , ' O K T ' , ' N O V ' , ' D E S ' ] [ $ i - 1 ]   } } :   { {   $ v a l   } } % " >  
                                                                                                                                 < / d i v >  
                                                                                                                                 @ p h p   $ x P o s   + =   $ x S t e p ;   @ e n d p h p  
                                                                                                                         @ e n d f o r  
                                                                                                                 < / d i v >  
                                                                                                                 < d i v   c l a s s = " f l e x   j u s t i f y - b e t w e e n   t e x t - [ 7 p x ]   t e x t - g r a y - 4 0 0   f o n t - b o l d   m t - 1 " >  
                                                                                                                         < s p a n > J A N < / s p a n >  
                                                                                                                         < s p a n > . . < / s p a n >  
                                                                                                                         < s p a n > { {   s t r t o u p p e r ( s u b s t r ( \ C a r b o n \ C a r b o n : : p a r s e ( $ s e s i _ w i g - > t a n g g a l _ p e l a k s a n a a n ) - > t r a n s l a t e d F o r m a t ( ' F ' ) , 0 , 3 ) )   } } < / s p a n >  
                                                                                                                 < / d i v >  
                                                                                                         < / d i v >  
                                                                                                 < / d i v >  
                                                                                         < / d i v >  
                                                                                 < / d i v >  
                                                                                  
                                                                                 < ! - -   T a b e l   W I G   p e r   U P 3   - - >  
                                                                                 < d i v   c l a s s = " o v e r f l o w - x - a u t o   b o r d e r   b o r d e r - g r a y - 3 0 0   r o u n d e d - m d   b g - w h i t e   s h a d o w - s m " >  
                                                                                         < t a b l e   c l a s s = " w - f u l l   t e x t - x s   t e x t - l e f t " >  
                                                                                                 < t h e a d   c l a s s = " b g - [ # d 9 e d f 7 ]   t e x t - g r a y - 8 0 0   u p p e r c a s e   f o n t - b o l d   t e x t - [ 1 0 p x ]   b o r d e r - b   b o r d e r - g r a y - 3 0 0 " >  
                                                                                                         < t r >  
                                                                                                                 < t h   r o w s p a n = " 2 "   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r   a l i g n - m i d d l e " > U N I T < / t h >  
                                                                                                                 < t h   c o l s p a n = " 3 "   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r   b o r d e r - b " > { {   s t r t o u p p e r ( $ n a m a B u l a n P r e v )   } } < / t h >  
                                                                                                                 < t h   c o l s p a n = " 3 "   c l a s s = " p x - 2   p y - 1 . 5   t e x t - c e n t e r   b o r d e r - b " > { {   s t r t o u p p e r ( $ n a m a B u l a n T a r g e t )   } } < / t h >  
                                                                                                         < / t r >  
                                                                                                         < t r >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r   b g - [ # e e f 7 f c ] " > T a r g e t < / t h >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r   b g - [ # e e f 7 f c ] " > R e a l i s a s i < / t h >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r   b g - [ # e e f 7 f c ] " > C a p a i a n   ( % ) < / t h >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r " > T a r g e t < / t h >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - c e n t e r " > R e a l i s a s i < / t h >  
                                                                                                                 < t h   c l a s s = " p x - 2   p y - 1   t e x t - c e n t e r " > C a p a i a n   ( % ) < / t h >  
                                                                                                         < / t r >  
                                                                                                 < / t h e a d >  
                                                                                                 < t b o d y   c l a s s = " d i v i d e - y   d i v i d e - g r a y - 2 0 0 " >  
                                                                                                         < ! - -   U I D   R o w   - - >  
                                                                                                         < t r   c l a s s = " b g - [ # f c f 8 e 3 ] " >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   f o n t - b o l d   w h i t e s p a c e - n o w r a p " > U I D   J a w a   B a r a t < / t d >  
                                                                                                                 < ! - -   p r e v   - - >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - b o l d   t e x t - g r a y - 5 0 0 " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ t a r g e t _ p r e v   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - b o l d   t e x t - g r a y - 5 0 0 " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ r e a l i s a s i _ p r e v   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - b o l d   t e x t - g r a y - 5 0 0   { {   ( $ w i g - > c a p a i a n _ p r e v   ? ?   0 )   > =   1 0 0   ?   ' t e x t - g r e e n - 6 0 0 '   :   ' '   } } " > { {   n u m b e r _ f o r m a t ( $ w i g - > c a p a i a n _ p r e v   ? ?   0 ,   2 )   } } % < / t d >  
                                                                                                                 < ! - -   c u r r e n t   - - >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - b o l d " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ t a r g e t   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - b o l d " > { {   n u m b e r _ f o r m a t ( $ w i g - > t o t a l _ r e a l i s a s i   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                 < t d   c l a s s = " p x - 2   p y - 1 . 5   t e x t - r i g h t   f o n t - b o l d " > { {   n u m b e r _ f o r m a t ( $ p c t U i d ,   2 )   } } % < / t d >  
                                                                                                         < / t r >  
                                                                                                         < ! - -   U P 3   R o w s   - - >  
                                                                                                         @ f o r e a c h ( $ f i l t e r e d U p 3 s B y W i g [ $ w i g - > i d ]   a s   $ u p 3 )  
                                                                                                                 @ p h p  
                                                                                                                         $ u D a t a   =   $ w i g U n i t D a t a [ $ w i g - > i d ] [ $ u p 3 - > i d ]   ? ?   n u l l ;  
                                                                                                                 @ e n d p h p  
                                                                                                                 < t r   c l a s s = " h o v e r : b g - g r a y - 5 0 " >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   w h i t e s p a c e - n o w r a p " > { {   $ u p 3 - > n a m e   } } < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   b g - [ # e e f 7 f c ] / 3 0 " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' p r e v ' ] [ ' t ' ]   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   b g - [ # e e f 7 f c ] / 3 0 " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' p r e v ' ] [ ' r ' ]   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t   f o n t - s e m i b o l d   b g - [ # e e f 7 f c ] / 3 0   { {   ( $ u D a t a [ ' p r e v ' ] [ ' p c t ' ]   ? ?   0 )   > =   1 0 0   ?   ' t e x t - g r e e n - 6 0 0 '   :   ' '   } } " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' p r e v ' ] [ ' p c t ' ]   ? ?   0 ,   2 )   } } % < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' c u r ' ] [ ' t ' ]   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   b o r d e r - r   b o r d e r - g r a y - 3 0 0   t e x t - r i g h t " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' c u r ' ] [ ' r ' ]   ? ?   0 ,   2 )   } } < / t d >  
                                                                                                                         < t d   c l a s s = " p x - 2   p y - 1 . 5   t e x t - r i g h t   f o n t - s e m i b o l d   { {   ( $ u D a t a [ ' c u r ' ] [ ' p c t ' ]   ? ?   0 )   > =   1 0 0   ?   ' t e x t - g r e e n - 6 0 0 '   :   ' '   } } " > { {   n u m b e r _ f o r m a t ( $ u D a t a [ ' c u r ' ] [ ' p c t ' ]   ? ?   0 ,   2 )   } } % < / t d >  
                                                                                                                 < / t r >  
                                                                                                         @ e n d f o r e a c h  
                                                                                                 < / t b o d y >  
                                                                                         < / t a b l e >  
                                                                                 < / d i v >  
                                                                         < / d i v >  
                                                                          
                                                                         < ! - -   K A N A N :   L M   C a r d s   C o n t a i n e r   - - >  
                                                                         < d i v   c l a s s = " w - f u l l   x l : w - [ 4 5 % ]   b g - w h i t e   p - 3   r o u n d e d - l g   b o r d e r   b o r d e r - g r a y - 2 0 0   s h a d o w - s m " >  
                                                                                 < d i v   c l a s s = " t e x t - [ 1 0 p x ]   f o n t - b o l d   t e x t - [ # 0 b 2 2 5 6 ]   u p p e r c a s e   m b - 3   b o r d e r - b   b o r d e r - g r a y - 2 0 0   p b - 2 " > P E R F O R M A   L E A D   M E A S U R E < / d i v >  
                                                                                 @ i f ( $ w i g L m s - > c o u n t ( )   >   0 )  
                                                                                         < d i v   s t y l e = " d i s p l a y :   g r i d ;   g r i d - t e m p l a t e - c o l u m n s :   r e p e a t ( a u t o - f i t ,   m i n m a x ( 2 1 0 p x ,   1 f r ) ) ;   g a p :   0 . 7 5 r e m ; " >  
                                                                                                 @ f o r e a c h ( $ w i g L m s   a s   $ i d x   = >   $ l m )  
                                                                                                         @ p h p  
                                                                                                                 $ l m P c t   =   $ l m - > c a p a i a n ;  
                                                                                                                 $ l m C o l o r   =   $ l m P c t   > =   1 0 0   ?   ' t e x t - g r e e n - 6 0 0 '   :   ' t e x t - r e d - 6 0 0 ' ;  
                                                                                                                 $ l m B a d g e   =   $ l m P c t   > =   1 0 0   ?   ' b g - g r e e n - 5 0 0 '   :   ' b g - r e d - 5 0 0 ' ;  
                                                                                                                 $ l m S t a t u s   =   $ l m P c t   > =   1 0 0   ?   ' E X C E E D E D   T A R G E T '   :   ' P E R F O R M A N C E   W A T C H ' ;  
                                                                                                                  
                                                                                                                 / /   F e t c h   L M   m e n a n g   k a l a h   d a t a   w h i c h   w a s   b u i l t   i n   c o n t r o l l e r  
                                                                                                                 $ m k L e v e l   =   $ i s U l p L e v e l   | |   $ i s U p 3 L e v e l   ?   ' u l p '   :   ' u p 3 ' ;  
                                                                                                                 $ m e n a n g   =   c o u n t ( $ l m M e n a n g K a l a h [ $ l m - > i d ] [ $ m k L e v e l ] [ ' m e n a n g ' ]   ? ?   [ ] ) ;  
                                                                                                                 $ k a l a h   =   c o u n t ( $ l m M e n a n g K a l a h [ $ l m - > i d ] [ $ m k L e v e l ] [ ' k a l a h ' ]   ? ?   [ ] ) ;  
                                                                                                         @ e n d p h p  
 
</div>
</div>

<!-- Trend Chart Section -->
            <div x-data="{ chartType: 'wig' }" class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        Grafik Tren Capaian WIG
                    </h3>
                </div>
                <div class="relative h-[450px] md:h-[400px] w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Map Widget (Dynamic) -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden" 
                 x-data="mapController(@js($wigs), @js($dynamicMapData))">
                <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/30">
                    <div class="w-full sm:w-auto">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center uppercase">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Peta Performa Capaian <span class="ml-1" x-text="mapLevel"></span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Distribusi pencapaian berdasarkan lokasi per Lead Measure</p>
                    </div>
                    <div class="flex bg-gray-200/50 p-1 rounded-lg w-full sm:w-auto overflow-x-auto">
                        <button @click="setMapLevel('up3')" :class="mapLevel === 'up3' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 sm:flex-none px-4 py-1.5 text-sm rounded-md transition-all">UP3</button>
                        <button @click="setMapLevel('ulp')" :class="mapLevel === 'ulp' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 sm:flex-none px-4 py-1.5 text-sm rounded-md transition-all">ULP</button>
                    </div>
                </div>
                
                <!-- WIG Tabs -->
                <div class="border-b border-gray-200 px-6 pt-4 bg-white">
                    <nav class="flex overflow-x-auto gap-6 hide-scrollbar" aria-label="Tabs">
                        <template x-for="wig in wigs" :key="wig.id">
                            <button @click="selectWig(wig.id)" 
                                    :class="selectedWig === wig.id ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-3 px-1 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-bold"
                                      :class="selectedWig === wig.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="wig.id"></span>
                                <span x-text="wig.judul"></span>
                            </button>
                        </template>
                    </nav>
                </div>
                
                <!-- LM Pills -->
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-100 flex flex-col gap-3" x-show="currentLms.length > 0" x-cloak>
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-xs font-bold text-slate-500 mr-2 uppercase tracking-wider">Pilih LM:</span>
                        <template x-for="(lm, index) in currentLms" :key="lm.id">
                            <button @click="selectLm(lm.id)"
                                    :class="selectedLm === lm.id ? 'bg-blue-600 text-white shadow-md border-blue-600 transform scale-105' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-blue-300'"
                                    class="px-4 py-1.5 text-xs font-bold rounded-full border transition-all duration-200">
                                <span x-text="(lm.judul_lm.match(/^LM-\d+/i) || [])[0] || ('LM ' + (index + 1))"></span>
                            </button>
                        </template>
                    </div>
                    
                    <!-- Selected LM Description -->
                    <div x-show="selectedLm !== null" class="bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 rounded-lg text-sm font-semibold flex items-start" x-transition>
                        <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="currentLms.find(l => l.id === selectedLm)?.judul_lm || ''"></span>
                    </div>
                </div>
                
                <!-- Map Container -->
                <div class="p-4 relative bg-gray-100/50" x-show="selectedLm !== null" x-cloak>
                    <div id="map" class="shadow-inner rounded-xl border border-gray-200 z-0" style="height: 480px;"></div>
                </div>

                <!-- Capaian LM Realtime Section (Tabel) -->
                <div class="border-t border-gray-200 bg-white p-6">
                  <div class="mb-4">
                      @if($latestSesiWig)
                          <div>
                              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                  <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                  Matriks Capaian Lead Measure
                              </h3>
                              <p class="text-xs text-gray-500 mt-1">s.d Tanggal {{ \Carbon\Carbon::parse($latestSesiWig->tanggal_pelaksanaan)->format('d/m/Y') }} (Sesi WIG Minggu Ke-{{ $latestSesiWig->minggu_ke }})</p>
                          </div>
                      @else
                          <div>
                              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                  <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                  Matriks Capaian Lead Measure
                              </h3>
                              <p class="text-xs text-gray-500 mt-1">Belum ada Sesi WIG di bulan ini</p>
                          </div>
                      @endif
                  </div>
                
                @if($latestSesiWig)
                <div class="mt-4">
                    @foreach($wigs as $wig)
                        @php $wigLms = $wigs->where('id', $wig->id)->first()->masterLms ?? collect(); @endphp
                        @if($wigLms->count() > 0)
                            @foreach($wigLms as $lm)
                                <!-- Container Tabel LM Dinamis -->
                                <div x-show="selectedLm === {{ $lm->id }}" x-cloak>
                                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start mb-8">
                                        <!-- Kiri: Tabel LM -->
                                        <div class="xl:col-span-3 w-full">
                                            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-xl border border-gray-200">
                                                <div class="p-3 bg-gray-50 border-b border-gray-200">
                                                    <h5 class="text-xs font-bold text-gray-800">{{ $lm->judul_lm }}</h5>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-300 text-[10px]">
                                                        <thead class="bg-gray-100">
                                                            <tr>
                                                                <th rowspan="2" class="px-3 py-2 border border-gray-300 text-left font-bold text-gray-800 sticky left-0 bg-gray-100 z-10">UNIT</th>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    <th colspan="4" class="px-2 py-1 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">WEEK {{ $sw->minggu_ke }}</th>
                                                                @endforeach
                                                            </tr>
                                                            <tr>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">REALISASI</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">PENCAPAIAN (%)</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-8" title="Tren terhadap Realisasi Minggu Sebelumnya">TREN</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            <!-- Baris UID Jabar -->
                                                            <tr class="bg-indigo-50 border-b-2 border-indigo-200">
                                                                <td class="px-3 py-1 border border-gray-300 font-black text-indigo-900 whitespace-nowrap sticky left-0 bg-indigo-50 z-10 uppercase">
                                                                    UID JABAR
                                                                </td>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    @php
                                                                        $uidTarget = $matrixTargets[$lm->id][1][$sw->id] ?? 0;
                                                                        $uidRealisasi = $matrixRealisasi[$lm->id][1][$sw->id] ?? 0;
                                                                        $uidPencapaian = $uidTarget > 0 ? min(100, round(($uidRealisasi / $uidTarget) * 100, 2)) : 0;
                                                                        $uidBgColor = $uidPencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                        $prevUidRealisasi = 0;
                                                                        $uidTrendIcon = '<span class="text-gray-400">-</span>';
                                                                        if ($prevSw) {
                                                                            $prevUidRealisasi = $matrixRealisasi[$lm->id][1][$prevSw->id] ?? 0;
                                                                            if ($uidRealisasi > $prevUidRealisasi) {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                            } else if ($uidRealisasi < $prevUidRealisasi) {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                            } else {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 2) }}</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidRealisasi, 2) }}</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black {{ $uidBgColor }}">{{ $uidPencapaian }}%</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-center bg-slate-50">{!! $uidTrendIcon !!}</td>
                                                                @endforeach
                                                            </tr>
                                                            @foreach($up3s as $up3)
                                                                @php
                                                                    $up3Ulps = $ulps->where('parent_id', $up3->id);
                                                                    $isExpanded = false;
                                                                @endphp
                                                                <tr class="hover:bg-slate-200 transition-colors bg-slate-100 cursor-pointer up3-row-rt" onclick="toggleUlpsRt('{{$lm->id}}-{{$up3->id}}')">
                                                                    <td class="px-3 py-1 border border-gray-300 font-bold text-indigo-900 whitespace-nowrap sticky left-0 bg-slate-100 z-10">
                                                                        <div class="flex items-center justify-between">
                                                                            <span>{{ $up3->name }}</span>
                                                                            <svg id="icon-rt-{{$lm->id}}-{{$up3->id}}" class="w-3 h-3 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                        </div>
                                                                    </td>
                                                                    @foreach($sesi_wigs_matrix as $sw)
                                                                        @php
                                                                            $up3Target = $matrixTargets[$lm->id][$up3->id][$sw->id] ?? 0;
                                                                            $up3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$sw->id] ?? 0;
                                                                            $up3Pencapaian = $up3Target > 0 ? min(100, round(($up3Realisasi / $up3Target) * 100, 2)) : 0;
                                                                            $up3BgColor = $up3Pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                            
                                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                            $prevUp3Realisasi = 0;
                                                                            $up3TrendIcon = '<span class="text-gray-400">-</span>';
                                                                            if ($prevSw) {
                                                                                $prevUp3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                                                if ($up3Realisasi > $prevUp3Realisasi) {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                                } else if ($up3Realisasi < $prevUp3Realisasi) {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                                } else {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-semibold">{{ number_format($up3Realisasi, 2) }}</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-bold {{ $up3BgColor }}">{{ $up3Pencapaian }}%</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-center bg-slate-50">{!! $up3TrendIcon !!}</td>
                                                                    @endforeach
                                                                </tr>
                                                                @foreach($up3Ulps as $u)
                                                                    <tr class="hover:bg-slate-50 transition-colors ulp-row-rt-{{$lm->id}}-{{$up3->id}} hidden">
                                                                        <td class="px-3 py-1 border border-gray-300 font-medium text-gray-800 whitespace-nowrap sticky left-0 bg-white z-10 pl-6">
                                                                            {{ $u->name }}
                                                                        </td>
                                                                        @foreach($sesi_wigs_matrix as $sw)
                                                                            @php
                                                                                $target = $matrixTargets[$lm->id][$u->id][$sw->id] ?? 0;
                                                                                $realisasi = $matrixRealisasi[$lm->id][$u->id][$sw->id] ?? 0;
                                                                                $pencapaian = $target > 0 ? min(100, round(($realisasi / $target) * 100, 2)) : 0;
                                                                                $bgColor = $pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                                
                                                                                $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                                $prevRealisasi = 0;
                                                                                $trendIcon = '<span class="text-gray-400">-</span>';
                                                                                if ($prevSw) {
                                                                                    $prevRealisasi = $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                                    if ($realisasi > $prevRealisasi) {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                                    } else if ($realisasi < $prevRealisasi) {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                                    } else {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            <td class="px-1 py-1 border border-gray-300 text-right">{{ number_format($target, 2) }}</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-right">{{ number_format($realisasi, 2) }}</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-right font-bold {{ $bgColor }}">{{ $pencapaian }}%</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-center">{!! $trendIcon !!}</td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Menang Kalah Widget -->
                                        <div class="xl:col-span-1 w-full sticky top-6">
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4" x-data="{ tabRt: 'up3' }">
                                                <h3 class="text-xs font-bold text-gray-900 mb-3 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                    Menang Kalah LM
                                                </h3>
                                                <div class="flex space-x-1 bg-gray-100/50 p-1 rounded-md mb-3 text-[10px] font-semibold">
                                                    <button @click="tabRt = 'up3'" :class="tabRt === 'up3' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1 rounded-sm transition-all">UP3</button>
                                                    <button @click="tabRt = 'ulp'" :class="tabRt === 'ulp' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1 rounded-sm transition-all">ULP</button>
                                                </div>

                                                <div x-show="tabRt === 'up3'" class="space-y-3">
                                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                                        <div class="bg-green-50 border border-green-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-green-600">{{ count($rtMenangKalah[$lm->id]['up3']['menang'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-red-600">{{ count($rtMenangKalah[$lm->id]['up3']['kalah'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                                        @foreach($rtMenangKalah[$lm->id]['up3']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($rtMenangKalah[$lm->id]['up3']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div x-show="tabRt === 'ulp'" class="space-y-3" style="display: none;">
                                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                                        <div class="bg-green-50 border border-green-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-green-600">{{ count($rtMenangKalah[$lm->id]['ulp']['menang'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-red-600">{{ count($rtMenangKalah[$lm->id]['ulp']['kalah'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                                        @foreach($rtMenangKalah[$lm->id]['ulp']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($rtMenangKalah[$lm->id]['ulp']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- Close x-show div -->
                                @endforeach
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            <!-- WIG Heatmap Container -->
            <div x-show="selectedWig !== null" class="mt-6 border-t border-gray-200 bg-white p-6" x-cloak>
                <div x-html="heatmapHtml"></div>
                <div x-show="loadingHeatmap" class="py-12 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 mx-auto text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat Heatmap WIG...
                </div>
            </div>

            <!-- End of Combined Map & Matrix Widget -->
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup Trend Chart
            const trendData = @json($trendData);
            const ctx = document.getElementById('trendChart').getContext('2d');
            
            // Register datalabels plugin
            Chart.register(ChartDataLabels);
            
            // Generate datasets
            const wigDatasets = [];
            const colors = [
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e'
            ];
            let colorIndex = 0;

            // Add WIG Progresses (Thick lines)
            Object.values(trendData.wig_progress).forEach(wig => {
                wigDatasets.push({
                    label: wig.name,
                    data: wig.data,
                    borderColor: colors[colorIndex % colors.length],
                    backgroundColor: colors[colorIndex % colors.length] + '33',
                    borderWidth: 4,
                    tension: 0.3,
                    fill: false
                });
                colorIndex++;
            });

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: wigDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        },
                        datalabels: {
                            align: 'top',
                            anchor: 'end',
                            formatter: function(value) {
                                return value + '%';
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            color: function(context) {
                                return context.dataset.borderColor;
                            },
                            backgroundColor: 'rgba(255, 255, 255, 0.7)',
                            borderRadius: 4,
                            padding: 2
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

        });

        // Alpine Map Controller
        function mapController(wigs, dynamicMapData) {
            return {
                wigs: wigs,
                dynamicMapData: dynamicMapData,
                selectedWig: null,
                currentLms: [],
                selectedLm: null,
                mapLevel: 'up3', // 'ulp' or 'up3'
                map: null,
                markers: [],

                init() {
                    // Initialize Leaflet Map (Centered around Bandung, West Java)
                    this.map = L.map('map').setView([-6.9147, 107.6098], 8);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        subdomains: 'abc',
                        maxZoom: 20
                    }).addTo(this.map);
                    
                    // Prevent grey boxes by forcing size recalculation
                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 500);

                    if (this.wigs.length > 0) {
                        this.selectWig(this.wigs[0].id);
                    }
                },

                selectWig(wigId) {
                    this.selectedWig = wigId;
                    let wig = this.wigs.find(w => w.id === wigId);
                    let rawLms = wig ? (wig.master_lms || []) : [];
                    this.currentLms = rawLms.slice().sort((a, b) => {
                        const numA = parseInt((a.judul_lm.match(/LM-?(\d+)/i) || [0, 999])[1]);
                        const numB = parseInt((b.judul_lm.match(/LM-?(\d+)/i) || [0, 999])[1]);
                        return numA - numB;
                    });
                    
                    if (this.currentLms.length > 0) {
                        this.selectLm(this.currentLms[0].id);
                    } else {
                        this.selectedLm = null;
                        this.updateMap();
                    }

                    this.fetchHeatmap();
                },

                loadingHeatmap: false,
                heatmapHtml: '',
                fetchHeatmap() {
                    if (!this.selectedWig) return;
                    this.loadingHeatmap = true;
                    this.heatmapHtml = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div></div>';
                    fetch(`/laporan-bulanan/preview?jenis=dashboard_heatmap&tahun={{ $tahun }}&bulan={{ $bulan }}&wig_id=${this.selectedWig}&_t=${new Date().getTime()}`)
                        .then(res => res.json())
                        .then(data => {
                            this.heatmapHtml = data.html;
                            this.loadingHeatmap = false;
                        })
                        .catch(err => {
                            console.error('Error fetching heatmap:', err);
                            this.loadingHeatmap = false;
                        });
                },

                selectLm(lmId) {
                    this.selectedLm = lmId;
                    this.updateMap();
                },
                
                setMapLevel(level) {
                    this.mapLevel = level;
                    this.updateMap();
                },

                updateMap() {
                    // Clear existing markers
                    this.markers.forEach(marker => this.map.removeLayer(marker));
                    this.markers = [];

                    if (!this.selectedLm || !this.dynamicMapData[this.selectedLm] || !this.dynamicMapData[this.selectedLm][this.mapLevel]) return;

                    let lmData = this.dynamicMapData[this.selectedLm][this.mapLevel];

                    lmData.forEach(loc => {
                        var color = '#ef4444'; // Merah (< 95%)
                        if (loc.progress >= 100) {
                            color = '#22c55e'; // Hijau (>= 100%)
                        } else if (loc.progress >= 95) {
                            color = '#eab308'; // Kuning (95% - 99.99%)
                        }
                        
                        var markerHtmlStyles = `
                            background-color: ${color};
                            width: 2rem;
                            height: 2rem;
                            display: block;
                            left: -1rem;
                            top: -1rem;
                            position: relative;
                            border-radius: 3rem 3rem 0;
                            transform: rotate(45deg);
                            border: 1px solid #FFFFFF;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                        `;

                        var icon = L.divIcon({
                            className: "custom-pin",
                            iconAnchor: [0, 24],
                            labelAnchor: [-6, 0],
                            popupAnchor: [0, -36],
                            html: `<span style="${markerHtmlStyles}"></span>`
                        });

                        var marker = L.marker([loc.lat, loc.lng], {icon: icon})
                            .addTo(this.map)
                            .bindPopup(`<b>${loc.name}</b><br>Performa: ${loc.progress}%`);
                            
                        this.markers.push(marker);
                    });
                }
            }
        }
    </script>
</x-app-layout>

