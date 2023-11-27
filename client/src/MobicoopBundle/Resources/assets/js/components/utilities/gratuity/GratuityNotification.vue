<template>
  <div>
    <v-dialog
      v-if="userGratuityNotification"
      v-model="dialog"
      persistent
      max-width="600"
    >
      <v-card>
        <v-card-actions class="justify-end">
          <v-btn
            elevation="0"
            color="white"
            @click="close"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-actions>
        <v-card-text v-html="template" />
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
  props:{
    userGratuityNotification:{
      type: Object,
      default: null
    }
  },
  data () {
    return {
      dialog: true,
    }
  },
  computed:{
    template(){
      return this.userGratuityNotification.template;
    }
  },
  methods:{
    close(){
      this.dialog = false;
    }
  }
}
</script>
