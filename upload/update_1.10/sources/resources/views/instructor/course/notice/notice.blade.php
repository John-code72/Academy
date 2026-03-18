 @php
     $notices = App\Models\NoticeBoard::where('id', $id)->first();
 @endphp

 <div class="card mb-3 border-0" style="background-color: #e8f8f5; border-radius: 8px;">
     <div class="card-body p-3">
         <div class="d-flex justify-content-between align-items-start">
             <div class="d-flex">
                 <div>
                     <h5 class="mb-1 fw-bold" style="color:#1abc9c">
                         {{ $notices->title }}
                     </h5>
                     <p class="mb-0 text-muted line-clamp-2"
                         style="font-size: 0.90rem; white-space: normal; word-break: break-word;">
                         {!! $notices->description !!}
                     </p>
                 </div>
             </div>
         </div>
     </div>
 </div>
