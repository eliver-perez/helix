<?php
    $title = "Recibo de Pago";
    $section = "Recibo de Pago";

    require_once __DIR__.'/../layout/title.php';
?>


<div class=" bg-white dark:bg-box-dark rounded-10 xl:px-[60px] px-[30px] xl:pt-[80px] pt-[30px] pb-[30px]">
    <div class="print-body">
        <div class="flex items-center sm:justify-between justify-center max-sm:flex-col gap-x-[30px] gap-y-[15px] xl:mb-[57px] mb-[30px]">
            <a href="index.html">
            <img class="inline-block object-contain max-w-[140px] w-full dark:hidden" src="images/logos/logo-dark.png" alt="dark">
            <img class="hidden object-contain h-full dark:inline-block" src="images/logos/logo-white.png" alt="light">
            </a>
            <address class="text-[14px] leading-[1.57] font-medium text-dark dark:text-title-dark not-italic">Admin
            Company<br> 795 Folsom Ave, Suite 600<br> San Francisco, CA 94107, USA<br>
            Reg.
            number : 245000003513</address>
        </div>
        <div class=" flex items-center md:justify-between justify-center flex-wrap xl:gap-x-[30px] gap-x-[15px] gap-y-[15px] xl:px-[50px] px-[30px] py-[30px] mb-[40px] bg-regularBG dark:bg-box-dark rounded-[20px]">
            <div class="">
            <h1 class="leading-[1.27] font-semibold text-dark dark:text-title-dark mb-[5px] text-[36px]">
                Recibo
            </h1>
            <div class="text-[15px] leading-[1.73] font-medium text-dark dark:text-title-dark">
                No: #642678
            </div>
            <div class="text-[15px] leading-[1.73] font-medium text-dark dark:text-title-dark">
                Date : Jan 17, 2020
            </div>
            </div>
            <div class="p-[20px] rounded-10 bg-white dark:bg-box-dark-up flex text-center flex-col justify-center">
            <img class="w-full h-[60px]" src="images/shop/qr.png" alt="qr">
            <div class="text-[14px] leading-[1.42] font-medium text-dark dark:text-title-dark">
                <?= $id; ?>
            </div>
            </div>
            <address class="not-italic font-normal capitalize text-body dark:text-subtitle-dark text-[15px]">
            <h5 class="text-[15px] leading-[1.6] font-medium text-dark dark:text-title-dark uppercase mb-[4px]">
                Invoice
                To:</h5>
            <span>Stanley Jones</span><br>
            <span>795 Folsom Ave, Suite 600</span><br>
            <span>San Francisco, CA 94107, USA</span>
            </address>
        </div>
        <div class="pb-[15px] max-xl:scrollbar max-xl:overflow-x-auto">
            <table class="min-w-full text-sm font-light text-start whitespace-nowrap">
            <thead>
                <tr>
                    <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-[25px] py-4 text-start text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden rounded-s-[4px]">
                        #</th>
                    <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-start text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden">
                        Product</th>
                    <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden">
                        Price</th>
                    <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden text-end">
                        Quantity</th>
                    <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-[25px] py-2.5 text-end text-light dark:text-title-dark text-[12px] font-medium border-none before:hidden rounded-e-[4px]">
                        total
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-box-dark">
                <tr class="group">
                    <td class="border-b border-regular dark:border-box-dark-up ps-[25px] pe-4 py-[23px] text-start last:text-end group-hover:bg-transparent before:hidden rounded-s-[4px] text-dark dark:text-title-dark">
                        1
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal text-start capitalize text-[14px] text-light dark:text-subtitle-dark group-hover:bg-transparent">
                        <h6 class="capitalize mb-[8px] leading-[21px] text-dark dark:text-title-dark text-[15px] font-medium">
                        Fiber base chair</h6>
                        <ul class="flex items-center flex-wrap gap-x-[20px] gap-y-[5px] mb-0 capitalize">
                        <li>
                            <span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">Size
                                :</span>
                            <span class="text-body dark:text-subtitle-dark text-[13px]">Medium</span>
                        </li>
                        <li>
                            <span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">
                                Color :</span>
                            <span class="text-body dark:text-subtitle-dark text-[13px]">Golden</span>
                        </li>
                        </ul>
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal text-center capitalize text-[14px] text-light dark:text-subtitle-dark group-hover:bg-transparent">
                        $248.66
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal text-end capitalize text-[14px] text-dark dark:text-title-dark group-hover:bg-transparent">
                        1
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 ps-1 pe-4 py-[23px] font-normal text-end capitalize group-hover:bg-transparent text-dark dark:text-title-dark">
                        $248.66
                    </td>
                </tr>
                <tr class="group">
                    <td class="border-b border-regular dark:border-box-dark-up ps-[25px] pe-4 py-[23px] text-start last:text-end group-hover:bg-transparent before:hidden rounded-s-[4px] text-dark dark:text-title-dark">
                        2
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal text-start capitalize text-[14px] text-light dark:text-subtitle-dark group-hover:bg-transparent">
                        <h6 class="capitalize mb-[8px] leading-[21px] text-dark dark:text-title-dark text-[15px] font-medium">
                        Leo Sodales Varius</h6>
                        <ul class="flex items-center flex-wrap gap-x-[20px] gap-y-[5px] mb-0 capitalize">
                        <li>
                            <span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">Size
                                :</span>
                            <span class="text-body dark:text-subtitle-dark text-[13px]">Large</span>
                        </li>
                        <li>
                            <span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">
                                Color :</span>
                            <span class="text-body dark:text-subtitle-dark text-[13px]">Gray</span>
                        </li>
                        </ul>
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal text-center capitalize text-[14px] text-light dark:text-subtitle-dark group-hover:bg-transparent">
                        $240
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 px-1  py-[23px] font-normal capitalize text-[14px] text-dark dark:text-title-dark group-hover:bg-transparent text-end">
                        2
                    </td>
                    </td>
                    <td class="border-b border-regular dark:border-box-dark-up xl:px-4 ps-1 pe-4 py-[23px] font-normal text-end capitalize group-hover:bg-transparent text-dark dark:text-title-dark">
                        $240
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="ps-[25px] pe-4 py-[23px]">
                    </td>
                    <td class="ps-[25px] pe-4 py-[23px]">
                        <div class="flex flex-col justify-end items-end text-[14px] font-medium text-body dark:text-subtitle-dark leading-[21px] gap-y-[15px] capitalize">
                        <div>
                            Subtotal :
                        </div>
                        <div>
                            discount :
                        </div>
                        <div>
                            Shipping charge :
                        </div>
                        <h6 class="font-medium text-dark dark:text-title-dark text-[16px] mt-2">
                            Total :
                        </h6>
                        </div>
                    </td>
                    <td class="ps-[25px] pe-4 py-[23px]">
                        <div class="flex flex-col justify-end items-end text-[14px] font-medium text-body dark:text-title-dark leading-[21px] gap-y-[15px] capitalize">
                        <div>
                            $1,690.26
                        </div>
                        <div>
                            -$126.30
                        </div>
                        <div>
                            $46.30
                        </div>
                        <h5 class="font-medium text-primary text-[18px] mt-2">
                            $1736.00
                        </h5>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>

    <div class="flex items-center justify-end flex-wrap gap-x-[10px] gap-y-[5px] xl:mt-[50px] mt-[30px]">
        <button class="print-btn capitalize border-1 border-regular bg-regular dark:bg-white text-light dark:text-dark bg-transparent text-[14px] font-semibold leading-[22px] inline-flex items-center justify-center rounded-[40px] px-[30px] h-[38px] transition duration-300 ease-in-out gap-[7px] dark:pointer-events-none" data-te-ripple-init data-te-ripple-color="dark">
            <i class="uil uil-print text-light dark:text-dark "></i>
            Print
        </button>
        <button class="invoice-btn capitalize border-1 border-regular bg-regular dark:bg-white text-light dark:text-dark bg-transparent text-[14px] font-semibold leading-[22px] inline-flex items-center justify-center rounded-[40px] px-[30px] h-[38px] transition duration-300 ease-in-out gap-[7px]" data-te-ripple-init data-te-ripple-color="dark">
            <i class="uil uil-message text-light dark:text-dark "></i>
            invoice
        </button>
        <button class="download-btn capitalize border-1 border-primary bg-[#8e1dce] text-white text-[14px] font-semibold leading-[22px] inline-flex items-center justify-center rounded-[40px] px-[30px] h-[38px] transition duration-300 ease-in-out gap-[7px] dark:pointer-events-none" data-te-ripple-init data-te-ripple-color="light">
            <i class="uil uil-download-alt "></i>
            Download
        </button>
    </div>
</div>

<script src="<?= asset('js/pos/index.js'); ?>"></script>

<script>
    var currentLink = '<?= base_url('pos'); ?>';
</script>