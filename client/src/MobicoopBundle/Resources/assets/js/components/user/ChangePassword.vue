<template>
  <v-content>
    <v-snackbar
      v-model="snackbar"
      :color="(this.errorUpdate)?'error':'success'"
      top
    >
      {{ (this.errorUpdate)?this.textSnackError:this.textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        Close
      </v-btn>
    </v-snackbar>
    <v-container
      fluid     
    >
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
          >
            <v-text-field
              v-model="password"
              :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
              :type="show1 ? 'text' : 'password'"
              name="password"
              :label="$t('models.user.password.placeholder')"
              required
              :rules="passwordRules"
              @click:append="show1 = !show1"
            />            
            <v-text-field
              v-model="passwordRepeat"
              :append-icon="show2 ? 'mdi-eye' : 'mdi-eye-off'"
              :type="show2 ? 'text' : 'password'"
              name="passwordRepeat"
              :label="$t('models.user.passwordRepeat.placeholder')"
              required
              :rules="passwordRepeatRules"
              @click:append="show2 = !show2"
            />            
            <v-btn
              :disabled="!valid"
              :loading="loading"
              color="success"
              type="button"
              rounded
              @click="validate"
            >
              {{ $t('ui.button.save') }}
            </v-btn>
          </v-form>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>
<script>
import axios from 'axios';
export default {
  props: {
  },
  data(){
    return {
      snackbar:false,
      loading:false,
      textSnackOk:this.$t('snackBar.user.passwordUpdated'),
      textSnackError:this.$t('snackBar.user.passwordUpdateError'),
      errorUpdate:false,
      valid:true,
      password: '',
      passwordRules: [
        v => !!v || this.$t('models.user.password.errors.required'),
      ],
      passwordRepeat: '',
      passwordRepeatRules: [
        v => !!v || this.$t('models.user.password.errors.required'),
        v => (!!v && v) === this.password || 'Les mots de passes ne sont pas identiques'
      ],
      show1: false,
      show2: false
    }
  },
  methods: {
    validate () {
      if (this.$refs.form.validate()) {
        this.changePassword();
      }
    },
    changePassword(){
      let params = new FormData();
      params.append("password",this.password);
      this.loading = true;
      axios
        .post("/user/password/update",params)
        .then(res => {
          this.errorUpdate = res.data.state;
          this.loading = false;
          this.snackbar = true;
        });      
    }
  }
}
</script>