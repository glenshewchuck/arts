# AWS Secrets Manager

This Drupal module adds a new key provider for the [Key module](https://www.drupal.org/project/key) - it allows you to encrypt data using [AWS Secrets Manager](https://aws.amazon.com/secrets-manager/).

## Get Started
This guide assumes you have an AWS account and working knowledge of AWS Secrets Manager and IAM, and the following resources provisioned in AWS.

* One or more secrets
* An IAM user with privileges to access the relevant secrets

Ensure this module and its dependencies are available in your codebase.

- https://drupal.org/project/key
- https://github.com/aws/aws-sdk-php

Enable the **AWS Secrets Manager** module.

@todo document the rest

### AWS Credentials

There are alternatives to configuring the AWS credentials in the admin form.

**settings.php**

```
$config['aws_secrets_manager.settings']['aws_key'] = 'foo';
$config['aws_secrets_manager.settings']['aws_secret'] = 'bar';
```

If you do not explicitly set AWS key and secret in config, it will fall back to:

* IAM Instance Profile
* Exported credentials in environment variables
* The default profile in a `~/.aws/credentials` file

See the AWS SDK Guide on [Credentials](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html).
