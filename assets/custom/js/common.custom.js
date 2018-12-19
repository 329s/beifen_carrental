/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($){
    if ($.custom == undefined) {
        $.custom = {};
    }
    
    $.custom.uiframework = 'easyui',
    
    $.custom.lan = {
        defaults: {
            sys: {
                prompt: 'Prompt',
                ok: 'OK',
                cancel: 'Cancel',
                sessionTimeoutOrSigninByOtherPleaseResignin: 'Your session were timeout or signined by other, please re-signin.',
            },
            
            role: {
                enabled: 'Enabled',
                disabled: 'Disabled',
            },
            
            bills: {
                timeRange: 'Time range',
                totalAmount: 'Total amount',
                totalOrders: 'Total orders',
                succeedAmount: 'Succeed amount',
                succeedOrders: 'Succeed orders',
            },
            
            vehicle: {
                newDriver: 'New driver',
                driverLisence: 'Driver lisence',
                newDriverLimited: 'New driver limited',
                driverLisenceLimited: 'Driver lisence limited',
                newDriverAllowed: 'New driver allowed',
                driverLisenceNotLimited: 'Driver lisence not limited',
                naturallyAspirated: 'Naturally aspirated',
                turboCharged: 'Turbocharged',
                engineFront: 'Engine front ',
                engineMiddle: 'Engine middle ',
                engineRear: 'Engine rear ',
                driverWheelFront: 'front-wheel drive',
                driverWheelRear: 'rear-wheel drive',
                driverWheelFull: '4-wheel drive',
                addVehicleBrand: 'Add vehicle brand',
                addVehicleSeries: 'Add vehicle series',
                vehicleBrand: 'Vehicle brand',
                vehicleSeries: 'Vehicle series',
                kilometer: 'kilometer',
                days: 'day(s)',
                overflow: 'over',
                left: 'left',
                drivingLisenceWouldExpired: 'The driving lisence would be expired, please confirm wether rent the car or not',
                youConfirmedToRentPleaseSubmitAgain: 'You confirmed to rent the car, please re-submit the order again.'
            },
            
            office: {
                nearAirPort: 'Near air port',
                nearTrainStation: 'Near train station',
                nearBusStation: 'Near bus station',
                nearSubway: 'Near subway',
                normal: 'Normal',
                closed: 'Closed',
            },
        },
    }

})(jQuery);

