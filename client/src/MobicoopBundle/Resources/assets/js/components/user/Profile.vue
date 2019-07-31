<template>
  <v-content>
    <v-container
      fluid     
    >
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-tabs
            slider-color="secondary"
            color="secondary"
            grow
          >
            <v-tab>{{ $t("navBar.buttons.myProfile.label") }}</v-tab>
            <v-tab-item>rien</v-tab-item>
            <v-tab>{{ $t("models.user.password.label") }}</v-tab>
            <v-tab-item><ChangePassword /></v-tab-item>
            <v-tab>{{ $t("navBar.buttons.myProposals.label") }}</v-tab>
            <v-tab-item>rien</v-tab-item>
          </v-tabs>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>
<script>
import ChangePassword from '@js/components/user/ChangePassword';
export default {
  components: {
    ChangePassword
  },
  props: {
  },
  data(){
    return {
      
    }
  }
}
</script>