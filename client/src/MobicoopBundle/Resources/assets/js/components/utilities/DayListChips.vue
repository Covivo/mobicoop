<template>
  <div>
    <v-chip
      :color="monColor"
      class="mr-2"
    >
      {{ dayName(daysList[0]) }}
    </v-chip>
    <v-chip
      :color="tueColor"
      class="mr-2"
    >
      {{ dayName(daysList[1]) }}
    </v-chip>
    <v-chip
      :color="wedColor"
      class="mr-2"
    >
      {{ dayName(daysList[2]) }}
    </v-chip>
    <v-chip
      :color="thuColor"
      class="mr-2"
    >
      {{ dayName(daysList[3]) }}
    </v-chip>
    <v-chip
      :color="friColor"
      class="mr-2"
    >
      {{ dayName(daysList[4]) }}
    </v-chip>
    <v-chip
      :color="satColor"
      class="mr-2"
    >
      {{ dayName(daysList[5]) }}
    </v-chip>
    <v-chip
      :color="sunColor"
      class="mr-2"
    >
      {{ dayName(daysList[6]) }}
    </v-chip>
  </div>
</template>
<script>
import Translations from "@translations/components/utilities/DayListChips.json";

export default {
  i18n: {
    messages: Translations,
  },
  props: {
    monActive:{
      type: Boolean,
      default: false
    },
    tueActive:{
      type: Boolean,
      default: false
    },
    wedActive:{
      type: Boolean,
      default: false
    },
    thuActive:{
      type: Boolean,
      default: false
    },
    friActive:{
      type: Boolean,
      default: false
    },
    satActive:{
      type: Boolean,
      default: false
    },
    sunActive:{
      type: Boolean,
      default: false
    },
    longDaysName:{
      type: Boolean,
      default: false
    },
    colorActive:{
      type: String,
      default: "success"
    },
    colorInactive:{
      type: String,
      default: "default"
    }
  },
  data() {
    return {
      daysList: this.$t('days.list'),
      mon:(this.monActive) ? true : false,
      tue:(this.tueActive) ? true : false,
      wed:(this.wedActive) ? true : false,
      thu:(this.thuActive) ? true : false,
      fri:(this.friActive) ? true : false,
      sat:(this.satActive) ? true : false,
      sun:(this.sunActive) ? true :false
    }
  },
  computed:{
    monColor() { if(this.mon) { return this.colorActive }else {return this.colorInactive}},
    tueColor() { if(this.tue) { return this.colorActive }else {return this.colorInactive}},
    wedColor() { if(this.wed) { return this.colorActive }else {return this.colorInactive}},
    thuColor() { if(this.thu) { return this.colorActive }else {return this.colorInactive}},
    friColor() { if(this.fri) { return this.colorActive }else {return this.colorInactive}},
    satColor() { if(this.sat) { return this.colorActive }else {return this.colorInactive}},
    sunColor() { if(this.sun) { return this.colorActive }else {return this.colorInactive}}
  },
  watch:{
    monActive(newValue){(newValue) ? this.mon = true : this.mon = false;},
    tueActive(newValue){(newValue) ? this.tue = true : this.tue = false;},
    wedActive(newValue){(newValue) ? this.wed = true : this.wed = false;},
    thuActive(newValue){(newValue) ? this.thu = true : this.thu = false;},
    friActive(newValue){(newValue) ? this.fri = true : this.fri = false;},
    satActive(newValue){(newValue) ? this.sat = true : this.sat = false;},
    sunActive(newValue){(newValue) ? this.sun = true : this.sun = false;}
  },
  methods:{
    dayName(day){
      return (this.longDaysName) ? this.$t('days.'+day+'longName') : this.$t('days.'+day+'.shortName');
    },
    clickDay(day){
      switch(day){
      case this.daysList[0]:
        this.mon != this.mon;
        break;
      case this.daysList[1]:
        this.tue != this.tue;
        break;
      case this.daysList[2]:
        this.wed != this.wed;
        break;
      case this.daysList[3]:
        this.thu != this.thu;
        break;
      case this.daysList[4]:
        this.fri != this.fri;
        break;
      case this.daysList[5]:
        this.sat != this.sat;
        break;
      case this.daysList[6]:
        this.sun != this.sun;
        break;
      }
    }
  }
}
</script>