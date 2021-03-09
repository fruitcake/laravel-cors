(function() {
  var CORS_SERVER;

  CORS_SERVER = 'localhost:9292';

  describe('CORS-CREDENTIALS', function() {

    it('should allow get resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/auth`, {
        method: 'GET',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('123');
        return done();
      })
    });

    it('should allow post resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/auth`, {
        method: 'POST',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('123');
        return done();
      })
    });

    it('should allow put resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/auth`, {
        method: 'PUT',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('123');
        return done();
      })
    });

    it('should not allow delete resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/auth`, {
        method: 'DELETE',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });

    it('should not reach delete resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'DELETE',
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
      headers.append('Authorization', 'Bearer 123');
      headers.append('X-Custom-Header', 'Nope');
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
  });

}).call(this);
