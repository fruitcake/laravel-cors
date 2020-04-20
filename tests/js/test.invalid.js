(function() {
  var CORS_SERVER;

  CORS_SERVER = 'localhost:9292';

  describe('CORS-INVALID', function() {
    it('should now allow access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'GET',
        mode: 'cors'
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });
    it('should not allow PUT access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'PUT',
        mode: 'cors'
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });

    it('should not allow post resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'POST',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });

    return it('should not allow post resource with wrong header', function(done) {
      const headers = new Headers();
      headers.append('X-Custom-Header', 'Nope');
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'POST',
        mode: 'cors',
        headers: headers
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });
  });

}).call(this);
