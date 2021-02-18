require("./bootstrap");
var uuid = require("uuid");
window.Vue = require("vue");

function getLocalTime(offset) {
  var d = new Date();
  localTime = d.getTime();
  localOffset = d.getTimezoneOffset() * 60000;
  utc = localTime + localOffset;
  var nd = new Date(utc + 3600000 * offset);
  return nd.getTime();
}

Vue.component("Timer", {
  template: `
        <div>
            <div v-if="statusType !== 'expired'">
                <div class="day">
                    <span class="number">{{ days }}</span>
                    <div class="format">{{ wordString.day }}</div>
                </div>
                <div class="hour">
                    <span class="number">{{ hours }}</span>
                    <div class="format">{{ wordString.hours }}</div>
                </div>
                <div class="min">
                    <span class="number">{{ minutes }}</span>
                    <div class="format">{{ wordString.minutes }}</div>
                </div>
                <div class="sec">
                    <span class="number">{{ seconds }}</span>
                    <div class="format">{{ wordString.seconds }}</div>
                </div>
            </div>
            <div v-else>
                <span class="badge badge-danger">{{ statusText }}</span>
            </div>
        </div>
    `,
  props: ["starttime", "endtime", "trans", "type"],
  data: function() {
    return {
      timer: "",
      wordString: {},
      start: "",
      end: "",
      interval: "",
      days: "",
      minutes: "",
      hours: "",
      seconds: "",
      message: "",
      statusType: "",
      statusText: "",
      timerType: "slider",
    };
  },
  created: function() {
    this.wordString = JSON.parse(this.trans);
    this.localUUID = uuid.v4();
  },
  mounted() {
    if (this.type) {
      this.timerType = this.type;
    }
    this.start = new Date(this.starttime).getTime();
    this.end = new Date(this.endtime).getTime();
    if (!window.timerEndTime) {
      window.timerEndTime = [];
    }
    window.timerEndTime[this.localUUID] = new Date(this.endtime).getTime();
    // Update the count down every 1 second
    this.timerCount(this.start, this.end);

    this.interval = setInterval(() => {
      this.timerCount(this.start);
    }, 1000);
    if (this.timerType != "slider") {
      Echo.channel("auction-bid").listen("BroadcastAuctionBid", (response) => {
        window.timerEndTime[this.localUUID] = new Date(
          response.end_time
        ).getTime();
      });
    }
  },
  methods: {
    timerCount: function(start) {
      // Get todays date and time
      var now = getLocalTime(-5);
      var end = window.timerEndTime[this.localUUID];

      // Find the distance between now an the count down date
      var distance = start - now;
      var passTime = end - now;

      if (distance < 0 && passTime < 0) {
        this.message = this.wordString.expired;
        this.statusType = "expired";
        this.statusText = this.wordString.status.expired;
        clearInterval(this.interval);
      } else if (distance < 0 && passTime > 0) {
        this.calcTime(passTime);
        this.message = this.wordString.running;
        this.statusType = "running"; 
        this.statusText = this.wordString.status.running;
      } else if (distance > 0 && passTime > 0) {
        this.calcTime(distance);
        this.message = this.wordString.upcoming;
        this.statusType = "upcoming";
        this.statusText = this.wordString.status.upcoming;
      } else {
        this.message = this.wordString.expired;
        this.statusType = "expired";
        this.statusText = this.wordString.status.expired;
        clearInterval(this.interval);
      }
      if(this.statusType != 'expired'){
        if(this.days == 0 && this.hours == 0 && this.minutes < 2 && this.minutes > 1){
          console.log("2 msj");
        }else if(this.days == 0 && this.hours == 0 && this.minutes < 1){
          console.log("1 msj");
        }
      }
    },
    calcTime: function(dist) {
      // Time calculations for days, hours, minutes and seconds
      this.days = Math.floor(dist / (1000 * 60 * 60 * 24));
      this.hours = Math.floor(
        (dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      this.minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
      this.seconds = Math.floor((dist % (1000 * 60)) / 1000);
    },
  },
});
