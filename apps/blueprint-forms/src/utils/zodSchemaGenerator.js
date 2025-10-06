import { z } from 'zod';

/**
 * Generate a Zod schema from the API schema response
 * @param {object} schema - The schema object from the API
 * @returns {z.ZodObject} Zod schema for validation
 */
export const generateZodSchema = (schema) => {
  if (!schema?.collection?.model?.fillable) {
    return z.object({});
  }

  const schemaShape = {};
  const fillable = schema.collection.model.fillable;
  const casts = schema.collection.model.casts || {};
  const fields = schema.fields || {};

  fillable.forEach((fieldName) => {
    const fieldConfig = fields[fieldName] || {};

    // Skip hidden fields
    if (fieldConfig.hidden) {
      return;
    }

    let fieldSchema;

    // Determine field type based on casts and config
    const cast = casts[fieldName];
    const configType = fieldConfig.type;

    // Handle different field types
    if (cast === 'boolean' || configType === 'checkbox') {
      fieldSchema = z.boolean();
    } else if (cast === 'integer' || cast === 'int') {
      fieldSchema = z.coerce.number().int();
    } else if (cast === 'float' || cast === 'double' || cast === 'decimal') {
      fieldSchema = z.coerce.number();
    } else if (cast === 'datetime' || cast === 'date') {
      fieldSchema = z.string().datetime().or(z.date());
    } else if (configType === 'email' || fieldName.includes('email')) {
      // Email field: validate format only if not empty
      fieldSchema = z.string().email('Invalid email address').or(z.literal(''));
    } else if (configType === 'select' || configType === 'textarea') {
      fieldSchema = z.string();
    } else {
      // Default to string
      fieldSchema = z.string();
    }

    // Apply validation rules from field config
    if (fieldConfig.required) {
      if (cast === 'boolean') {
        fieldSchema = fieldSchema.refine((val) => val === true, {
          message: `${fieldConfig.label || fieldName} is required`,
        });
      } else if (configType === 'email' || fieldName.includes('email')) {
        // For required email: must be valid email (not empty)
        fieldSchema = z.string().min(1, `${fieldConfig.label || fieldName} is required`).email('Invalid email address');
      } else {
        fieldSchema = fieldSchema.min(1, `${fieldConfig.label || fieldName} is required`);
      }
    } else {
      // Make field optional - email already handles empty with .or(z.literal(''))
      if (!(configType === 'email' || fieldName.includes('email'))) {
        fieldSchema = fieldSchema.optional();
      }
    }

    // Add min/max length for strings
    if (fieldConfig.minLength && typeof fieldSchema._def.typeName === 'string') {
      fieldSchema = fieldSchema.min(fieldConfig.minLength,
        `Minimum ${fieldConfig.minLength} characters required`);
    }
    if (fieldConfig.maxLength && typeof fieldSchema._def.typeName === 'string') {
      fieldSchema = fieldSchema.max(fieldConfig.maxLength,
        `Maximum ${fieldConfig.maxLength} characters allowed`);
    }

    // Add min/max for numbers
    if (fieldConfig.min !== undefined && (cast === 'integer' || cast === 'int' || cast === 'float')) {
      fieldSchema = fieldSchema.min(fieldConfig.min,
        `Minimum value is ${fieldConfig.min}`);
    }
    if (fieldConfig.max !== undefined && (cast === 'integer' || cast === 'int' || cast === 'float')) {
      fieldSchema = fieldSchema.max(fieldConfig.max,
        `Maximum value is ${fieldConfig.max}`);
    }

    // Add pattern validation for strings
    if (fieldConfig.pattern) {
      fieldSchema = fieldSchema.regex(new RegExp(fieldConfig.pattern),
        fieldConfig.patternMessage || 'Invalid format');
    }

    // Add enum validation for select fields
    if (fieldConfig.options && Array.isArray(fieldConfig.options)) {
      const values = fieldConfig.options.map(opt =>
        typeof opt === 'string' ? opt : opt.value
      );
      fieldSchema = z.enum(values);
      if (!fieldConfig.required) {
        fieldSchema = fieldSchema.optional();
      }
    }

    schemaShape[fieldName] = fieldSchema;
  });

  return z.object(schemaShape);
};

/**
 * Get a human-readable field name from a field configuration
 * @param {string} fieldName - The field name
 * @param {object} fieldConfig - The field configuration
 * @returns {string} Human-readable field name
 */
export const getFieldLabel = (fieldName, fieldConfig = {}) => {
  return fieldConfig.label || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
