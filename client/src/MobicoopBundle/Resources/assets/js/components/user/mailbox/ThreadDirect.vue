<template>
  <v-main>
    <v-container class="window-scroll px-0">
      <v-card
        class="mx-auto mt-2 pt-1 pb-1"
        :class="selected ? 'primary lighten-4' : ''"
        :outlined="(unreadMessages>0) ? false : true"
        style="border-style:none;"
        @click="click()"
      >
        <v-row class="ma-0">
          <v-col class="col-3 text-center ma-0 pa-0">
            <v-avatar>
              <img :src="avatar">
            </v-avatar>
          </v-col>
          <v-col class="col-3 ma-0 pa-0">
            <v-card-text class="pa-0">
              <span
                class="text-h6 font-weight-light secondary--text"
              >
                {{ name }}
              </span>
            </v-card-text>
          </v-col>
          <v-col class="col-3 ma-0 pa-0">
            <v-card-text
              class="pa-0 ma-0 text-right pr-2 font-italic"
            >
              {{ formateDate }}
            </v-card-text>
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
      </v-card>
    </v-container>
  </v-main>
</template>
<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadDirect/";

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
      locale: localStorage.getItem("X-LOCALE"),
      currentUnreadMessages: this.unreadMessages
    }
  },
  computed: {
    formateDate(){
      return moment.utc(this.date).format("L");
    },
    name() {
      return this.givenName + " " + this.shortFamilyName;
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
      this.currentUnreadMessages = 0;
      this.emit();
    },
    // toggleSelected(){
    //   this.selected = !this.selected;
    // },
    emit(){
      this.$emit("toggleSelected",{idMessage:this.idMessage});
      this.$emit("idMessageForTimeLine",
        {
          type:"Direct",
          idMessage:this.idMessage,
          idRecipient:this.idRecipient,
          avatar:this.avatar,
          name:this.name,
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