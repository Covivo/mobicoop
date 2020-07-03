<template>
  <div>
    <v-chip
      v-for="(currentDay, index) in daysList"
      :key="index"
      :color="chipColor(currentDay)"
      class="mr-2"
    >
      {{ dayName(currentDay) }}
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
      daysList: this.$t('days.list')
    }
  },  
  methods:{
    dayName(day){
      return (this.longDaysName) ? this.$t('days.'+day+'longName') : this.$t('days.'+day+'.shortName');
    },
    chipColor(day){
      switch(day){
      case this.daysList[0]: return (this.monActive) ? this.colorActive : this.colorInactive;
      case this.daysList[1]: return (this.tueActive) ? this.colorActive : this.colorInactive;
      case this.daysList[2]: return (this.wedActive) ? this.colorActive : this.colorInactive;
      case this.daysList[3]: return (this.thuActive) ? this.colorActive : this.colorInactive;
      case this.daysList[4]: return (this.friActive) ? this.colorActive : this.colorInactive;
      case this.daysList[5]: return (this.satActive) ? this.colorActive : this.colorInactive;
      case this.daysList[6]: return (this.sunActive) ? this.colorActive : this.colorInactive;
      default: return this.colorInactive;
      }
    }
  }
}
</script>