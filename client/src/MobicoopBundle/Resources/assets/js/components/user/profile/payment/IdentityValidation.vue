<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="12"
        md="10"
        class="text-center text-h6 pt-4"
        :class="(!formActive && validationAskedDate==null) ? 'text--disabled' : ''"
      >
        {{ $t('title') }}
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        cols="12"
        class="text-justify pt-4 font-italic"
        :class="(!formActive && validationAskedDate==null) ? 'text--disabled' : ''"
        v-html="$t('text')"
      />
    </v-row>
    <v-row v-if="validationAskedDate!=null || currentValidationStatus > 0">
      <v-col cols="12">
        <v-card
          flat
          class="text-center"
        >
          <!-- Pending -->
          <v-card-text
            v-if="currentValidationStatus==0"
            class="warning--text"
          >
            <v-icon class="warning--text">
              mdi-magnify-scan
            </v-icon>
            {{ $t('status.pending') }}
          </v-card-text>
          <!-- Validated -->
          <v-card-text
            v-else-if="currentValidationStatus==1"
            class="success--text"
          >
            <v-icon class="success--text">
              mdi-check-circle-outline
            </v-icon>
            {{ $t('status.validated') }}
          </v-card-text>
          <!-- Refused -->
          <v-card-text
            v-else-if="currentValidationStatus==2"
            class="error--text"
          >
            <v-icon class="error--text">
              mdi-close-circle-outline
            </v-icon>
            {{ $t('status.refused.'+refusalReason) }}
          </v-card-text>
          <!-- Outdated -->
          <v-card-text
            v-else-if="currentValidationStatus==3"
            class="warning--text"
          >
            <v-icon class="warning--text">
              mdi-alert-circle-outline
            </v-icon>
            {{ $t('status.outdated') }}
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row
      v-if="(validationAskedDate==null || currentValidationStatus >= 2)"
      justify="center"
    >
      <v-col
        cols="10"
        class="text-left pt-4 font-italic"
      >
        <v-row>
          <v-col>
            <template>
              <v-file-input
                v-model="document"
                :accept="validationDocsAuthorizedExtensions"
                :label="$t('fileInput.label')"
                :disabled="!formActive"
                :rules="identityProofRules"
                show-size
                counter
              />
            </template>
            <template>
              <v-file-input
                v-model="optionalDocument"
                :accept="validationDocsAuthorizedExtensions"
                :label="$t('fileInput.labelOptional')"
                :disabled="!formActive"
                :rules="optionalIdentityProofRules"
                :hint="$t('fileInput.hintOptional')"
                persistent-hint
                show-size
                counter
              />
            </template>
            <p
              class="text-justify font-italic ml-6 mt-5"
              :class="(!formActive && validationAskedDate==null) ? 'text--disabled' : ''"
            >
              {{ $t('fileInput.tooltip') }}
            </p>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="2">
            <v-btn
              rounded
              color="secondary"
              :disabled="!formActive || document == null || disabledSendFile"
              :loading="loading"
              @click="send"
            >
              {{ $t('send') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/payment/IdentityValidation/";

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
    canBePaid:{
      type: Boolean,
      default: false
    },
    validationDocsAuthorizedExtensions:{
      type: String,
      default: null
    },
    paymentProfileStatus: {
      type: Number,
      default: 0
    },
    validationStatus: {
      type: Number,
      default: 0
    },
    validationAskedDate: {
      type: Object,
      default: null
    },
    refusalReason: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      maxDocSize: 6291456,
      minDocSize: 32768,
      document:null,
      optionalDocument:null,
      loading:false,
      identityProofRules: [
        value => !value ||  (value.size < this.maxDocSize && value.size > this.minDocSize) || this.$t("fileInput.error")
      ],
      optionalIdentityProofRules: [
        value => value &&  (value.size < this.maxDocSize && value.size > this.minDocSize) || this.$t("fileInput.error")
      ],
      currentValidationStatus: this.validationStatus
    }
  },
  computed:{
    formActive(){
      if(this.paymentProfileStatus==0){
        // No active payment profile
        return false;
      }
      else if(this.currentValidationStatus==0 && this.validationAskedDate==null){
        // Active payment profile but identity validation hasn't been asked
        return true;
      }
      else if(this.currentValidationStatus >= 2){
        // Identity doccument rejected or outdated
        return true;
      }
      return false;
    },
    mandatoryDocumentValid(){
      if(!this.document){
        return false;
      }
      if(this.document.size > this.maxDocSize || this.document.size < this.minDocSize){
        return false;
      }
      return true;
    },
    optionalDocumentValid(){
      if(!this.optionalDocument){
        return true;
      }
      if(this.optionalDocument.size > this.maxDocSize || this.optionalDocument.size < this.minDocSize){
        return false;
      }
      return true;
    },
    disabledSendFile(){
      if(!this.mandatoryDocumentValid || !this.optionalDocumentValid){
        return true;
      }
      return false;
    }
  },
  methods: {
    send(){
      let sendDocument = new FormData();
      sendDocument.append("document", this.document);
      if(this.optionalDocument){
        console.log("optionalDocument")
        sendDocument.append("optionalDocument", this.optionalDocument);
      }
      this.loading = true;
      maxios
        .post(this.$t('sendUrl'), sendDocument,
          {
            headers:{
              'content-type': 'multipart/form-data'
            }
          })
        .then(res => {
          this.document = null;
          this.optionalDocument = null;
          this.loading = false;
          this.currentValidationStatus = 0;
          this.$emit("identityDocumentSent",res.data);
        });

    }
  }
}
</script>
