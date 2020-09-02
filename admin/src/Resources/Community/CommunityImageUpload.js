import React from 'react';
import { FormDataConsumer, TextInput } from 'react-admin';
import { useForm } from 'react-final-form';

import ImageUpload from '../../components/media/ImageUpload';

const CommunityImageUpload = ({ label }) => {
  const form = useForm();

  return (
    <FormDataConsumer>
      {(formDataProps) => {
        return (
          <ImageUpload
            imageId={
              formDataProps.formData.images &&
              formDataProps.formData.images[0] &&
              formDataProps.formData.images[0].id
            }
            onChange={(image) => {
              // For an unknown reason, changing image doesn't enable "save" button
              // The form is always marked as pristine. So I use the "updatedDate" field to enable it
              form.change('updatedDate', new Date());
              image.id && form.change('images', ['/images/' + image.id]);
            }}
            referenceField="community"
            referenceId={formDataProps.formData.originId}
            label={label}
          />
        );
      }}
    </FormDataConsumer>
  );
};

export default CommunityImageUpload;
