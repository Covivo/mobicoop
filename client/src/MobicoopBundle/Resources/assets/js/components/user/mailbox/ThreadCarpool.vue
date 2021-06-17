<template>
  <v-main>
    <v-container
      class="window-scroll px-0"
      :class="unreadMessages>0 ? 'font-italic' : ''"
    >
      <v-card
        class="mx-0 mt-2 pt-1 pb-1"
        :class="selected ? 'primary lighten-5' : ''"
        outlined
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
                    class="text-h6 font-weight-light secondary--text"
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
              <v-col class="col-9 text-left pa-0 ma-0">
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
                >{{ formateFromDate }} <span v-if="formateFromTime">{{ $t("at") }} {{ formateFromTime }}</span></span>
                <span
                  v-else
                  class="font-italic"
                >{{ regularCarpoolDays }}</span>
              </v-col>
              <v-col
                v-if="currentUnreadMessages>0"
                cols="3"
                class="subtitle-2 pa-0 ma-0 text-right"
              >
                <v-chip class="secondary">
                  {{ currentUnreadMessages }}&nbsp;<v-icon class="white--text">
                    mdi-eye-off-outline
                  </v-icon>
                </v-chip>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card>
    </v-container>
  </v-main>
</template>
<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadCarpool/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
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
    },
    idAskSelected:{
      type: Number,
      default: null
    },
    solidary:{
      type: Boolean,
      default: false
    },
    blockerId:{
      type: Number,
      default: null
    },
    unreadMessages:{
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      selected: this.selectedDefault,
      locale: this.$i18n.locale,
      currentUnreadMessages: this.unreadMessages
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
      if(this.criteria.monCheck==true){carpoolDays.push(this.$t('Mon'));}
      if(this.criteria.tueCheck==true){carpoolDays.push(this.$t('Tue'));}
      if(this.criteria.wedCheck==true){carpoolDays.push(this.$t('Wed'));}
      if(this.criteria.thuCheck==true){carpoolDays.push(this.$t('Thu'));}
      if(this.criteria.friCheck==true){carpoolDays.push(this.$t('Fri'));}
      if(this.criteria.satCheck==true){carpoolDays.push(this.$t('Sat'));}
      if(this.criteria.sunCheck==true){carpoolDays.push(this.$t('Sun'));}
      return carpoolDays.join(", ");
    },
    name() {
      return (this.givenName != null && this.shortFamilyName != null ) ? this.givenName + " " + this.shortFamilyName : (this.$t("userDelete"));
    }
  },
  watch: {
    selectedDefault(){
      this.selected = this.selectedDefault;
    }
  },
  mounted() { 
    if (this.idAskSelected == this.idAsk) {
      this.emit()
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    click(){
      this.currentUnreadMessages = 0;
      this.emit();
    },
    toggleSelected(){
      this.selected = !this.selected;
    },
    emit(){
      this.$emit("toggleSelected",{idAsk:this.idAsk});
      this.$emit("idMessageForTimeLine",
        {
          type:this.solidary ? "Solidary" : "Carpool",
          idMessage:this.idMessage,
          idRecipient:this.idRecipient,
          name:this.name,
          avatar:this.avatar,
          idAsk:this.idAsk,
          blockerId:this.blockerId,
          formerUnreadMessages:this.unreadMessages
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