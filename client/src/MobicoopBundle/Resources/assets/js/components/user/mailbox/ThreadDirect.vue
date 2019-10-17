<template>
  <v-content>
    <v-container class="window-scroll px-0">
      <v-card
        class="mx-auto mt-2 pt-1 pb-1"
        :class="selected ? 'primary' : ''"
        outlined
        tile
        style="border-style:none;"
        @click="click()"
      >
        <v-row class="ma-0">
          <v-col class="col-3 text-center ma-0 pa-0">
            <v-avatar>
              <!-- For now, we are not supporting the avatar. We show an icon instead -->
              <v-icon class="display-2">
                mdi-account-circle
              </v-icon>
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
    familyName: {
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
      selected: this.selectedDefault
    }
  },
  computed: {
    formateDate(){
      return moment(this.date).format("ddd DD MMM YYYY");
    },
    name() {
      return this.givenName + " " + this.familyName.substr(0, 1).toUpperCase() + ".";
    },
  },
  watch: {
    selectedDefault(){
      this.selected = this.selectedDefault;
    }
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