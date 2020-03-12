<div class="row" style="display: {{count($bank_accounts) ? 'block' : 'none'}}" id="bank_accounts">
@foreach($bank_accounts as $bank_account)
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="info-box">
            <span class="custom-info-box bg-yellow">
                {{$bank_account->name}}
            </span>

            <div class="info-box-content">
                <div style="margin-top: 5px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Balance:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($bank_account->balance) ? $bank_account->balance : 0)}}
                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Dep.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($bank_account->total_deposit) ? $bank_account->total_deposit : 0)}}
                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Wit.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($bank_account->total_withdraw) ? $bank_account->total_withdraw : 0)}}
                        </span>
                    </div>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
@endforeach
</div>

<div class="row" style="display: {{count($service_accounts) ? 'block' : 'none'}}" id="service_accounts">
    @foreach($service_accounts as $service_account)
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="info-box">
            <span class="custom-info-box bg-green">
                {{$service_account->name}}
            </span>

            <div class="info-box-content">
                <div style="margin-top: 5px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Balance:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($service_account->balance) ? $service_account->balance : 0)}}
                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Dep.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($service_account->total_deposit) ? $service_account->total_deposit : 0)}}
                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Wit.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number">{{(!empty($service_account->total_withdraw) ? $service_account->total_withdraw : 0)}}
                        </span>
                    </div>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
@endforeach
</div>