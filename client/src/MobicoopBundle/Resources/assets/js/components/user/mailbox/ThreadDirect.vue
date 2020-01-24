<template>
  <v-content>
    <v-container class="window-scroll px-0">
      <v-card
        class="mx-auto mt-2 pt-1 pb-1"
        :class="selected ? 'primary lighten-4' : ''"
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
          <v-col class="col-3 ma-0 pa-0">
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
      </v-card>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";

export default {
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
          name:this.name
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