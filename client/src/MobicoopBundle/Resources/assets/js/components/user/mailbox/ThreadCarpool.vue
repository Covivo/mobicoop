<template>
  <v-content>
    <v-container class="window-scroll px-0">
      <v-card
        class="mx-0 mt-2 pt-1 pb-1"
        :class="selected ? 'primary lighten-5' : ''"
        outlined
        tile
        style="border-style:none;"
        @click="click()"
      >
        <v-row class="ma-0">
          <v-col class="col-3 text-center ma-0 pa-0">
            <v-avatar>
              <img :src="avatar">
            </v-avatar>
          </v-col>
          <v-col class="col-8 ma-0 pa-0">
            <v-row
              align="start"
            >
              <v-col class="col-7 ma-0 pa-0">
                <v-card-text class="pa-0">
                  <span
                    class="title font-weight-light secondary--text"
                  >
                    {{ name }}
                  </span>
                </v-card-text>
              </v-col>
              <v-col class="col-5 ma-0 pa-0">
                <v-card-text
                  class="pa-0 ma-0 text-right pr-2 font-italic"
                >
                  {{ formateDate }}
                </v-card-text>
              </v-col>
            </v-row>

            <v-row>
              <v-col class="col-8 text-left pa-0 ma-0">
                <span
                  class="font-weight-light"
                >
                  {{ origin }}        
                  <v-icon color="tertiairy">
                    mdi-arrow-right
                  </v-icon>
                  {{ destination }}
                </span>
                <br>
                <span
                  v-if="criteria.frequency==1"
                  class="font-italic"
                >{{ formateFromDate }} <span v-if="formateFromTime">{{ $t("ui.infos.misc.at") }} {{ formateFromTime }}</span></span>
                <span
                  v-else
                  class="font-italic"
                >{{ regularCarpoolDays }}</span>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";
import Translations from "@translations/components/user/mailbox/ThreadCarpool.json";

export default {
  i18n: {
    messages: Translations,
  },
  props: {
    avatar: {
      type:String,
      default:null
    },
    givenName: {
      type: String,
      default:""
    },
    shortFamilyName: {
      type: String,
      default:""
    },
    date: {
      type: String,
      default: null
    },
    origin: {
      type: String,
      default: null
    },
    destination: {
      type: String,
      default: null
    },
    criteria: {
      type: Object,
      default: null
    },
    idMessage: {
      type: Number,
      default: null
    },
    idRecipient: {
      type: Number,
      default: null
    },
    selectedDefault: {
      type: Boolean,
      default: false
    },
    idAsk:{
      type: Number,
      default: null
    }
  },
  data() {
    return {
      selected: this.selectedDefault,
      locale: this.$i18n.locale
    }
  },
  computed: {
    formateDate(){
      return moment.utc(this.date).format("L");
    },
    formateFromDate(){
      return moment.utc(this.criteria.fromDate).format("ddd DD MMM YYYY");
    },
    formateFromTime(){
      return (this.criteria.fromTime) ? moment.utc(this.criteria.fromTime).format("HH")+"h"+moment.utc(this.criteria.fromTime).format("mm") : null;
    },
    regularCarpoolDays(){
      let carpoolDays = [];
      if(this.criteria.monCheck!==null){carpoolDays.push(this.$t('Mon'));}
      if(this.criteria.tueCheck!==null){carpoolDays.push(this.$t('Tue'));}
      if(this.criteria.wedCheck!==null){carpoolDays.push(this.$t('Wed'));}
      if(this.criteria.thuCheck!==null){carpoolDays.push(this.$t('Thu'));}
      if(this.criteria.friCheck!==null){carpoolDays.push(this.$t('Fri'));}
      if(this.criteria.satCheck!==null){carpoolDays.push(this.$t('Sat'));}
      if(this.criteria.sunCheck!==null){carpoolDays.push(this.$t('Sun'));}
      return carpoolDays.join(", ");
    },
    name() {
      return (this.givenName != null && this.shortFamilyName != null ) ? this.givenName + " " + this.shortFamilyName : (this.$t("userDelete"));
    },
  },
  watch: {
    selectedDefault(){
      this.selected = this.selectedDefault;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    click(){
      this.emit();
    },
    toggleSelected(){
      this.selected = !this.selected;
    },
    emit(){
      this.$emit("toggleSelected",{idAsk:this.idAsk});
      this.$emit("idMessageForTimeLine",
        {
          type:"Carpool",
          idMessage:this.idMessage,
          idRecipient:this.idRecipient,
          name:this.name,
          idAsk:this.idAsk
        }
      );
    }
  }
}
</script>
<style lang="scss" scoped>
.window-scroll{
  max-height:600px;
  overflow:auto;
}
</style>